<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\UserModel;
use App\Core\Mailer;
use App\Core\Functions;

class ProfileController extends Controller {

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user']['id'];

        $model = new UserModel();
        $user = $model->findById($userId);
        $username = $_SESSION['user']['name']; // 🔥 here is the name
        $logs = $model->getUserLogs($username);

        $this->loadView('profile/profile', [
                                                'user' => $user,
                                                'logs' => $logs
                                            ]);

    }

    public function updateProfile() {
        header('Content-Type: application/json');
        ob_clean();
        $this->requireLogin();

        $userId = $_SESSION['user']['id'];
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            return;
        }

        $model = new UserModel();
        $updated = $model->updateProfile($userId, $name, $email);

        if ($updated) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            Logger::info("User updated their profile: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not update profile.']);
        }
    }

    public function changePassword() {
        header('Content-Type: application/json');
        ob_clean();
        $this->requireLogin();

        $userId = $_SESSION['user']['id'];
        $current = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
        $new = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
        $confirm = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

        if (!$current || !$new || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            return;
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'The new password does not match.']);
            return;
        }

        $model = new UserModel();
        $user = $model->findById($userId); // ✅ NECESSARY
        
        if (!password_verify($current, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            return;
        }

        $success = $model->updatePasswordProfile($userId, $new);
        

        if ($success) {
            Mailer::sendPasswordChangedEmail($user['email'], $user['name']);
            Logger::info("User changed their password: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
            // Create notification
            $this->notificationModel->create($userId, 'Your password was updated successfully.');
        } else {
            echo json_encode(['success' => false, 'message' => 'Error changing the password.']);
        }
    }


    public function toggleDarkMode() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Session not started']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $isDark = isset($_POST['dark_mode']) && $_POST['dark_mode'] == 1;

        $model = new UserModel();
        $success = $model->setDarkMode($userId, $isDark);

        if ($success) {                       
            Functions::updateUserField($userId, 'dark_mode', $isDark);

            Logger::info("User $userId applied dark mode: " . ($isDark ? "Yes" : "No"));

            echo json_encode(['success' => true, 'darkMode' => $isDark]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not update.']);
        }
    }



    public function uploadAvatar()
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authorized.']);
            return;
        }

        $userId = $_SESSION['user']['id'];

        // ✅ 1. Subir imagen temporal al servidor
        $upload = Functions::uploadAvatar($_FILES['avatar'], $userId);

        if (!$upload['success']) {
            echo json_encode($upload);
            return;
        }

        $file = $upload['file']; // contiene name, ext, size, type, relative_path, full_path

        $model = new UserModel();

        // ✅ 2. Guardar metadata en base de datos y obtener ID del avatar
        $avatarId = $model->saveAvatarFile($userId, $file);

        if (!$avatarId) {
            if (file_exists($file['full_path'])) {
                unlink($file['full_path']);
            }
            Logger::error("Failed to save avatar in database.");
            echo json_encode(['success' => false, 'message' => 'Could not save the avatar in the database.']);
            return;
        }

        // ✅ 3. Renombrar el archivo temporal usando el ID de la base de datos
        $ext = pathinfo($file['full_path'], PATHINFO_EXTENSION);
        $newFileName = $avatarId . '.' . $ext;
        $newFullPath = dirname($file['full_path']) . '/' . $newFileName;
        $newRelativePath = dirname($file['relative_path']) . '/' . $newFileName;

        if (!rename($file['full_path'], $newFullPath)) {
            $model->deleteAvatarRecord($userId);
            Logger::error("Could not rename temporary avatar to $newFileName");
            echo json_encode(['success' => false, 'message' => 'Error saving the avatar.']);
            return;
        }

        // Actualizar registro en base de datos con el nuevo nombre y path
        $updated = $model->updateAvatarFileName($avatarId, $newFileName, $newRelativePath);

        if (!$updated) {
            $model->deleteAvatarRecord($userId);
            if (file_exists($newFullPath)) {
                unlink($newFullPath);
            }
            Logger::error("Failed to update avatar name in the database.");
            echo json_encode(['success' => false, 'message' => 'Could not update the avatar.']);
            return;
        }


        // ✅ 4. Actualizar el JSON del usuario       
        $jsonUpdated = Functions::updateUserField($userId, 'avatar_path', $newRelativePath);

        if (!$jsonUpdated) {
            $model->deleteAvatarRecord($userId);
            if (file_exists($newFullPath)) {
                unlink($newFullPath);
            }
            Logger::error("Failed to write user JSON file.");
            echo json_encode(['success' => false, 'message' => 'Could not save the avatar JSON.']);
            return;
        }

        Logger::info("User $userId updated their avatar.");

        // ✅ 5. Devolver respuesta final al frontend
        echo json_encode([
            'success' => true,
            'message' => 'Avatar updated successfully.',
            'avatar' => BASE_URL . 'assets/' . $newRelativePath
        ]);
    }


    public function changeLanguage() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authorized.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);
        $allowed = ['es', 'en']; // Idiomas permitidos

        if (!in_array($language, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Idioma no permitido.']);
            return;
        }

        $model = new UserModel();
        $dbUpdated = $model->setLanguage($userId, $language);
        $jsonUpdated = Functions::updateUserField($userId, 'language', $language);       

        if ($dbUpdated && $jsonUpdated) {
            Logger::info("User $userId changed language to $language");
            echo json_encode(['success' => true, 'message' => 'Language updated.']);
        } else {
            Logger::info("User $userId error changing language to $language");
            echo json_encode(['success' => false, 'message' => 'Could not update the language.']);
        }
    }


    public function deleteAccount() {
        $this->requireLogin();
        header('Content-Type: application/json');
        ob_clean();

        $userId = $_SESSION['user']['id'];

        $model = new UserModel();

        // Cambiar estado a 2 = Eliminado
        if ($model->setUserInactive($userId, 3) && $model->requestAccountClosure($userId)) {
            Logger::info("Account deletion request ID: $userId.");
            
            session_destroy(); // 🔥 Cerramos la sesión
            
            echo json_encode(['success' => true, 'message' => 'Your account has been deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not delete your account.']);
        }
    }





}

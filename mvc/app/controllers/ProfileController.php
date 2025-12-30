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
        $username = $_SESSION['user']['name']; // 🔥 aquí el nombre
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
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        $model = new UserModel();
        $updated = $model->updateProfile($userId, $name, $email);

        if ($updated) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            Logger::info("Usuario actualizó su perfil: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil.']);
        }
    }

    public function changePassword() {
        header('Content-Type: application/json');
        ob_clean();
        $this->requireLogin();

        $userId = $_SESSION['user']['id'];
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña no coincide.']);
            return;
        }

        $model = new UserModel();
        $user = $model->findById($userId); // ✅ NECESARIO
        
        if (!password_verify($current, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta.']);
            return;
        }

        $success = $model->updatePasswordProfile($userId, $new);
        

        if ($success) {
            Mailer::sendPasswordChangedEmail($user['email'], $user['name']);
            Logger::info("Usuario cambió su contraseña: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Contraseña cambiada correctamente.']);
            // Crear notificación
            $this->notificationModel->create($userId, 'Tu contraseña fue actualizada correctamente.');
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña.']);
        }
    }


    public function toggleDarkMode() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $isDark = isset($_POST['dark_mode']) && $_POST['dark_mode'] == 1;

        $model = new UserModel();
        $success = $model->setDarkMode($userId, $isDark);

        if ($success) {                       
            Functions::updateUserField($userId, 'dark_mode', $isDark);

            Logger::info("Usuario $userId aplicó modo oscuro: " . ($isDark ? "Sí" : "No"));

            echo json_encode(['success' => true, 'darkMode' => $isDark]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar.']);
        }
    }



    public function uploadAvatar()
    {
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
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
            Logger::error("Fallo al guardar avatar en base de datos.");
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar el avatar en la base de datos.']);
            return;
        }

        // ✅ 3. Renombrar el archivo temporal usando el ID de la base de datos
        $ext = pathinfo($file['full_path'], PATHINFO_EXTENSION);
        $newFileName = $avatarId . '.' . $ext;
        $newFullPath = dirname($file['full_path']) . '/' . $newFileName;
        $newRelativePath = dirname($file['relative_path']) . '/' . $newFileName;

        if (!rename($file['full_path'], $newFullPath)) {
            $model->deleteAvatarRecord($userId);
            Logger::error("No se pudo renombrar el avatar temporal a $newFileName");
            echo json_encode(['success' => false, 'message' => 'Error al guardar el avatar.']);
            return;
        }

        // Actualizar registro en base de datos con el nuevo nombre y path
        $updated = $model->updateAvatarFileName($avatarId, $newFileName, $newRelativePath);

        if (!$updated) {
            $model->deleteAvatarRecord($userId);
            if (file_exists($newFullPath)) {
                unlink($newFullPath);
            }
            Logger::error("Fallo al actualizar el nombre del avatar en la base de datos.");
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el avatar.']);
            return;
        }


        // ✅ 4. Actualizar el JSON del usuario       
        $jsonUpdated = Functions::updateUserField($userId, 'avatar_path', $newRelativePath);

        if (!$jsonUpdated) {
            $model->deleteAvatarRecord($userId);
            if (file_exists($newFullPath)) {
                unlink($newFullPath);
            }
            Logger::error("Fallo al escribir el archivo JSON del usuario.");
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar el JSON del avatar.']);
            return;
        }

        Logger::info("Usuario $userId actualizó su avatar.");

        // ✅ 5. Devolver respuesta final al frontend
        echo json_encode([
            'success' => true,
            'message' => 'Avatar actualizado con éxito.',
            'avatar' => BASE_URL . 'assets/' . $newRelativePath
        ]);
    }


    public function changeLanguage() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $language = $_POST['language'] ?? 'es';
        $allowed = ['es', 'en']; // Idiomas permitidos

        if (!in_array($language, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Idioma no permitido.']);
            return;
        }

        $model = new UserModel();
        $dbUpdated = $model->setLanguage($userId, $language);
        $jsonUpdated = Functions::updateUserField($userId, 'language', $language);       

        if ($dbUpdated && $jsonUpdated) {
            Logger::info("Usuario $userId cambió idioma a $language");
            echo json_encode(['success' => true, 'message' => 'Idioma actualizado.']);
        } else {
            Logger::info("Usuario $userId error al cambiar el idioma a $language");
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el idioma.']);
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
            Logger::info("Solicitud de eliminación de su cuenta ID: $userId.");
            
            session_destroy(); // 🔥 Cerramos la sesión
            
            echo json_encode(['success' => true, 'message' => 'Tu cuenta ha sido eliminada.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar tu cuenta.']);
        }
    }





}

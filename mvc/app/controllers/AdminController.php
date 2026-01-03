<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Functions;
use App\Core\Logger;
use App\Models\UserModel;
use App\Core\Mailer;
use App\Models\LogModel; // Import LogModel

class AdminController extends Controller {
    /**
     * Display user management view
     */
    public function manageUsers() {
        $this->requireAdmin();

        $model = new UserModel();
        $users = $model->getAllUsers();
        $roles = $model->getAllRoles();
        $statuses = $model->getAllAccountStatuses(); // 🔥 New

        $this->loadView('admin/admin_users', compact('users', 'roles','statuses'));
    }

    /**
     * Change a user's role via AJAX
     */
    public function changeRole() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        ob_clean();

        // Verify CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Access attempt with invalid CSRF");
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        if ($userId && $roleId) {
            $model = new UserModel();
            if ($model->updateUserRole($userId, $roleId)) {
                $roleName = $model->getRoleNameById($roleId);
                Logger::info("Administrator changed the role of user ID $userId to '$roleName'");

                echo json_encode([
                    'success' => true,
                    'message' => "Role updated to <strong>$roleName</strong>."
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating role.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid data for role change.'
            ]);
        }

        exit;
    }

    public function deleteUser() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        ob_clean();
        
        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId) {
            $model = new UserModel();
            if ($model->deleteById($userId)) {
                Logger::info("User deleted ID $userId");
                Functions::deleteUserJson($userId);
                echo json_encode(['success' => true, 'message' => 'User deleted.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not delete user.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        }
        exit;
    }

    public function createUser() {
        header('Content-Type: application/json');
        ob_clean();

        // Verify CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Access attempt with invalid CSRF ". $_POST['csrf_token'] . '--'. $_SESSION['csrf_token']);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }

        $name = Functions::sanitize($_POST['name'] ?? '');
        $email = Functions::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_id = $_POST['role_id'] ?? 2;
        $is_active = $_POST['is_active'] ?? 0;

        if (!$name || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            return;
        }

        $model = new UserModel();

        if ($model->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
            return;
        }

        // ✅ Generate token if the user is not active
        $token = (int)$is_active === 0 ? Functions::generateToken() : null;
        $created = $model->createUserByAdmin($name, $email, $password, $role_id, $is_active, $token);

        if ($created) {
            if ($token) {
                Mailer::sendActivationEmail($email, $name, $token);
                Logger::info("Activation email sent to $email (inactive user created)");
            }

            $user = $model->findByEmail($email);
            Functions::generateUserJson($user['id']);
            $this->notificationModel->create($user['id'], "Welcome to the system $name","INFO");            
            $model->createUserPreferences($user['id']);
            Functions::updateUserField($user['id'], 'role_id', $role_id);

            echo json_encode([
                'success' => true,
                'message' => 'User created successfully.',
                'id' => $model->getLastInsertId()
            ]);
            

        } else {
            echo json_encode(['success' => false, 'message' => 'Could not create user.']);
        }
    }


    public function updateUser() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        ob_clean();

        // Verify CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Access attempt with invalid CSRF");
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $name = Functions::sanitize($_POST['name'] ?? '');
        $email = Functions::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 1);

        if (!$id || !$name || !$email || !$roleId) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }

        $model = new UserModel();
        if ($model->updateUser($id, $name, $email, $password, $roleId, $isActive)) {
            Functions::updateUserField($id, 'role_id', $roleId);
            Logger::info("User ID $id updated by admin.");
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user.']);
        }
        exit;
    }

    /**
     * Display system logs
     */
    public function viewLogs() {
        $this->requireAdmin();

        $model = new LogModel();
        $logs = $model->getAllLogs();

        $this->loadView('admin/logs', compact('logs'));
    }

}
?>
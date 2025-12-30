<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Functions;
use App\Core\Logger;
use App\Models\UserModel;
use App\Core\Mailer;

class AdminController extends Controller {
    /**
     * Mostrar vista de administración de usuarios
     */
    public function manageUsers() {
        $this->requireAdmin();

        $model = new UserModel();
        $users = $model->getAllUsers();
        $roles = $model->getAllRoles();
        $statuses = $model->getAllAccountStatuses(); // 🔥 Nuevo

        $this->loadView('admin/admin_users', compact('users', 'roles','statuses'));
    }

    /**
     * Cambiar el rol de un usuario vía AJAX
     */
    public function changeRole() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        ob_clean();

        // Verificar CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Intento de acceso con CSRF inválido");
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        if ($userId && $roleId) {
            $model = new UserModel();
            if ($model->updateUserRole($userId, $roleId)) {
                $roleName = $model->getRoleNameById($roleId);
                Logger::info("Administrador cambió el rol del usuario ID $userId a '$roleName'");

                echo json_encode([
                    'success' => true,
                    'message' => "Rol actualizado a <strong>$roleName</strong>."
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el rol.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Datos inválidos para el cambio de rol.'
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
                Logger::info("Usuario eliminado ID $userId");
                Functions::deleteUserJson($userId);
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el usuario.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de usuario inválido.']);
        }
        exit;
    }

    public function createUser() {
        header('Content-Type: application/json');
        ob_clean();

        // Verificar CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Intento de acceso con CSRF inválido ". $_POST['csrf_token'] . '--'. $_SESSION['csrf_token']);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }

        $name = Functions::sanitize($_POST['name'] ?? '');
        $email = Functions::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_id = $_POST['role_id'] ?? 2;
        $is_active = $_POST['is_active'] ?? 0;

        if (!$name || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        $model = new UserModel();

        if ($model->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
            return;
        }

        // ✅ Generar token si el usuario no está activo
        $token = (int)$is_active === 0 ? Functions::generateToken() : null;
        $created = $model->createUserByAdmin($name, $email, $password, $role_id, $is_active, $token);

        if ($created) {
            if ($token) {
                Mailer::sendActivationEmail($email, $name, $token);
                Logger::info("Correo de activación enviado a $email (usuario creado inactivo)");
            }

            $user = $model->findByEmail($email);
            Functions::generateUserJson($user['id']);
            $this->notificationModel->create($user['id'], "Bienvenido al sistema $name","INFO");            
            $model->createUserPreferences($user['id']);
            Functions::updateUserField($user['id'], 'role_id', $role_id);

            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'id' => $model->getLastInsertId()
            ]);
            

        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear el usuario.']);
        }
    }


    public function updateUser() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        ob_clean();

        // Verificar CSRF
        if (!Functions::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            Logger::warning("Intento de acceso con CSRF inválido");
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $name = Functions::sanitize($_POST['name'] ?? '');
        $email = Functions::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 0);
        $isActive = (int)($_POST['is_active'] ?? 1);

        if (!$id || !$name || !$email || !$roleId) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        $model = new UserModel();
        if ($model->updateUser($id, $name, $email, $password, $roleId, $isActive)) {
            Functions::updateUserField($id, 'role_id', $roleId);
            Logger::info("Usuario ID $id actualizado por admin.");
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario.']);
        }
        exit;
    }


}
?>
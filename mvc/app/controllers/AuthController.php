<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Functions;
use App\Core\Logger;
use App\Core\Mailer;
use App\Models\UserModel;

class AuthController extends Controller {
    /**
     * Mostrar el formulario de registro
     */
    public function registerForm() {
            $this->loadView('auth/register');
        }

    /**
     * Procesar el registro del usuario
     */
    public function register() {
        header('Content-Type: application/json');
        ob_clean();

        $name = trim(Functions::sanitize($_POST['name'] ?? ''));
        $email = trim(Functions::sanitize($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Validaciones en backend
        if (!$name || !$email || !$password) {
            Logger::warning("Registro incompleto");
            echo json_encode([
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ]);
            return;
        }

        // Validar formato de correo
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Logger::warning("Correo inválido en registro: $email");
            echo json_encode([
                'success' => false,
                'message' => 'El correo electrónico no es válido.'
            ]);
            return;
        }

        // Validar longitud de contraseña
        if (strlen($password) < 6) {
            echo json_encode([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ]);
            return;
        }

        $userModel = new UserModel();

        // Evitar duplicados
        if ($userModel->emailExists($email)) {
            Logger::warning("Intento de registro con email existente: $email");
            echo json_encode([
                'success' => false,
                'message' => 'El correo ya está registrado.'
            ]);
            return;
        }

        if ($userModel->nameExists($name)) {
            Logger::warning("El nombrede usuario ya esta en uso: $name");
            echo json_encode([
                'success' => false,
                'message' => 'El nombrede usuario ya esta en uso.'
            ]);
            return;
        }

        $token = Functions::generateToken();
        $user = $userModel->findByEmail($email);

        if ($userModel->createUser($name, $email, $password, $token)) {
            if (Mailer::sendActivationEmail($email, $name, $token)) {                
                Functions::generateUserJson($user['id']);
                $userModel->createUserPreferences($user['id']);
                Functions::updateUserField($user['id'], 'role_id', 1);
                echo json_encode([
                    'success' => true,
                    'message' => 'Registro exitoso. Revisa tu correo para activar la cuenta.'
                ]);                
            } else {
                Logger::error("Fallo al enviar correo de activación a $email");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al enviar el correo de activación.'
                ]);
            }
            $this->notificationModel->create($user['id'], "Bienvenido al sistema $name","INFO");
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo completar el registro.'
            ]);
        }
    }



    /**
     * Activar cuenta mediante token
     */
    public function activate() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $this->setFlashAlert("Token no proporcionado.", "info");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->findByToken($token);

        if (!$user) {
            Logger::warning("Token de activación inválido: $token");
            $this->setFlashAlert("Token inválido o ya utilizado.", "info");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        if ($userModel->activateUser($token)) {
            Logger::info("Usuario activado: {$user['email']}");

            $this->setFlashAlert("Cuenta activada correctamente. Ya puedes iniciar sesión.", "success");

            header("Location: " . BASE_URL . "auth/login");
            exit;
        } else {
            $this->setFlashAlert("Error al activar la cuenta.", "danger");
        }
    }



    /**
     * Mostrar formulario de login
     */
    public function loginForm() {
            $this->loadView('auth/login');
        }

        /**
         * Procesar login
         */
    public function login() {
        header('Content-Type: application/json');
        ob_clean();

        // Seguridad: bloquear tras múltiples intentos fallidos
        $ipKey = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
        $blockKey = 'login_block_' . $_SERVER['REMOTE_ADDR'];

        // Si está bloqueado, comprobar tiempo
        // Si está bloqueado, comprobar si el tiempo ya expiró
        if (isset($_SESSION[$blockKey])) {
            if (time() < $_SESSION[$blockKey]) {
                $remaining = $_SESSION[$blockKey] - time();
                echo json_encode([
                    'success' => false,
                    'message' => "Demasiados intentos fallidos. Inténtalo de nuevo en $remaining segundos."
                ]);
                return;
            } else {
                // 💡 El bloqueo expiró → se limpian variables y se dan 5 intentos nuevos
                unset($_SESSION[$blockKey]);
                unset($_SESSION[$ipKey]);
            }
        }


        $email = trim(Functions::sanitize($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Validación básica
        if (!$email || !$password) {
            Logger::warning("Login fallido: campos vacíos.");
            echo json_encode(['success' => false, 'message' => 'Correo y contraseña son obligatorios.']);            
            return;
        }



        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Logger::warning("Login fallido: correo no registrado -> $email");
            echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
            return;
        }

        // Contraseña incorrecta
        if (!password_verify($password, $user['password'])) {
            $_SESSION[$ipKey] = ($_SESSION[$ipKey] ?? 0) + 1;

            if ($_SESSION[$ipKey] >= 5) {
                $_SESSION[$blockKey] = time() + 30; // Bloquear por 30 segundos
                Logger::warning("Login bloqueado por 30s para IP {$_SERVER['REMOTE_ADDR']}");
                echo json_encode([
                    'success' => false,
                    'message' => "Demasiados intentos fallidos. Espera 30 segundos."
                ]);
                return;
            }

            Logger::warning("Login fallido: contraseña incorrecta para $email");
            echo json_encode(['success' => false, 'message' => 'Credenciales inválidas.']);
            return;
        }

        if ((int)$user['is_active'] == 0) {
            Logger::info("Login bloqueado: cuenta inactiva -> $email");
            echo json_encode(['success' => false, 'message' => 'Tu cuenta aún no ha sido Activada.']);
            return;
        }

        if ((int)$user['is_active'] == 2) {
            Logger::info("Login bloqueado: cuenta inactiva -> $email");
            echo json_encode(['success' => false, 'message' => 'Tu cuenta esta Bloqueada.']);
            return;
        }

        if ((int)$user['is_active'] == 3) {
            $closure = $userModel->getClosureInfo($user['id']);

            if ($closure) {
                $requestedAt = strtotime($closure['requested_at']);
                $now = time();
                $daysElapsed = ($now - $requestedAt) / (60 * 60 * 24);

                if ($daysElapsed < 30) {
                    // ✅ Menos de 30 días: Revivir cuenta
                    $userModel->reactivateUser($user['id']);
                    Logger::info("Usuario $email reactivó su cuenta tras $daysElapsed días.");
                    // Permitir seguir autenticando normal
                } else {
                    // ❌ Más de 30 días, mantener bloqueo
                    Logger::info("Login bloqueado: cuenta en cierre definitivo -> $email");
                    echo json_encode(['success' => false, 'message' => 'Tu cuenta ya fue cerrada.']);
                    return;
                }
            } else {
                // ❌ No se encuentra solicitud de cierre (raro)
                Logger::warning("Estado 3 pero sin registro en account_closures -> $email");
                echo json_encode(['success' => false, 'message' => 'Tu cuenta no está disponible.']);
                return;
            }
        }


        // ✅ Sesión segura
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],         
        ];

        unset($_SESSION[$ipKey]);
        unset($_SESSION[$blockKey]);

        Logger::info("Inicio de sesión exitoso: $email");
        //$this->notificationModel->create($user['id'], "Bienvenido al sistema","INFO");

        // Después de validar al usuario
        $pref = (new UserModel())->getByUserId($user['id']);
        $_SESSION['dark_mode'] = $pref['dark_mode'] ?? 0;


        echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso.']);
    }


    public function logout() {
        session_start();        
        session_unset();      // Elimina todas las variables de sesión
        session_destroy();    // Destruye la sesión
        session_start();
        $this->setFlashAlert("Session cerrada con exito");
        // Redirigir al login
        header("Location: " . BASE_URL);
        exit;
    }

    public function forgotPasswordForm() {
        $this->loadView('auth/forgot-password');
    }


    public function sendResetLink() {
        header('Content-Type: application/json');
        ob_clean();

        $email = Functions::sanitize($_POST['email'] ?? '');

        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'El correo es obligatorio.']);
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Logger::warning("Intento de recuperación con correo no registrado: $email");
            echo json_encode(['success' => false, 'message' => 'Si el correo está registrado, recibirás instrucciones.']);
            return;
        }

        // Generar token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en base de datos
        if ($userModel->storePasswordResetToken($user['id'], $token, $expiresAt)) {
            // Enviar correo
            if (Mailer::sendPasswordResetEmail($email, $user['name'], $token)) {
                Logger::info("Token de recuperación generado y enviado a $email");
                echo json_encode(['success' => true, 'message' => 'Hemos enviado un enlace a tu correo para restablecer tu contraseña.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo de recuperación.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo generar el token de recuperación.']);
        }
    }


    public function resetPasswordForm() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $this->setFlashAlert("Token inválido","danger");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        $userModel = new UserModel();
        $record = $userModel->getPasswordResetByToken($token);

        // Verificar si el token existe y no ha expirado
        if (!$record || strtotime($record['expires_at']) < time()) {
            Logger::warning("Intento de usar token inválido o expirado: $token");
            $this->setFlashAlert("El enlace para cambiar tu contraseña ha expirado o es inválido.","info");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }


        $this->loadView('auth/reset-password', ['token' => $token]);
    }


    public function resetPassword() {
        header('Content-Type: application/json');
        ob_clean();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$token || !$password || !$confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }

        $userModel = new UserModel();
        $resetEntry = $userModel->getPasswordResetByToken($token);

        if (!$resetEntry) {
            Logger::warning("Token inválido para reset: $token");
            echo json_encode(['success' => false, 'message' => 'Token inválido o expirado.']);
            return;
        }

        $userId = $resetEntry['user_id'];
        $user = $userModel->findById($userId); // ✅ NECESARIO

        // Cambiar la contraseña
        $success = $userModel->updatePassword($userId, $password);

        if ($success) {
            $userModel->deletePasswordReset($token); // Eliminar el token           
            // ✅ Enviar correo de confirmación
            Mailer::sendPasswordChangedEmail($user['email'], $user['name']);
            Logger::info("Usuario cambió su contraseña: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
        } else {
            Logger::error("Error al actualizar contraseña para usuario ID: $userId");
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la contraseña.']);
        }
    }


}
?>
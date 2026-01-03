<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Functions;
use App\Core\Logger;
use App\Core\Mailer;
use App\Models\UserModel;

class AuthController extends Controller {
    /**
     * Show registration form
     */
    public function registerForm() {
            $this->loadView('auth/register');
        }

    /**
     * Process user registration
     */
    public function register() {
        header('Content-Type: application/json');
        ob_clean();

        $name = trim(Functions::sanitize($_POST['name'] ?? ''));
        $email = trim(Functions::sanitize($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Backend validations
        if (!$name || !$email || !$password) {
            Logger::warning("Incomplete registration");
            echo json_encode([
                'success' => false,
                'message' => 'All fields are mandatory.'
            ]);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Logger::warning("Invalid email during registration: $email");
            echo json_encode([
                'success' => false,
                'message' => 'The email address is not valid.'
            ]);
            return;
        }

        // Validate password length
        if (strlen($password) < 6) {
            echo json_encode([
                'success' => false,
                'message' => 'The password must be at least 6 characters long.'
            ]);
            return;
        }

        $userModel = new UserModel();

        // Avoid duplicates
        if ($userModel->emailExists($email)) {
            Logger::warning("Registration attempt with existing email: $email");
            echo json_encode([
                'success' => false,
                'message' => 'The email is already registered.'
            ]);
            return;
        }

        if ($userModel->nameExists($name)) {
            Logger::warning("The username is already in use: $name");
            echo json_encode([
                'success' => false,
                'message' => 'The username is already in use.'
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
                    'message' => 'Registration successful. Check your email to activate your account.'
                ]);                
            } else {
                Logger::error("Failed to send activation email to $email");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending the activation email.'
                ]);
            }
            $this->notificationModel->create($user['id'], "Welcome to the system $name","INFO");
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Registration could not be completed.'
            ]);
        }
    }



    /**
     * Activate account via token
     */
    public function activate() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $this->setFlashAlert("Token not provided.", "info");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->findByToken($token);

        if (!$user) {
            Logger::warning("Invalid activation token: $token");
            $this->setFlashAlert("Invalid or already used token.", "info");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        if ($userModel->activateUser($token)) {
            Logger::info("User activated: {$user['email']}");

            $this->setFlashAlert("Account successfully activated. You can now log in.", "success");

            header("Location: " . BASE_URL . "auth/login");
            exit;
        } else {
            $this->setFlashAlert("Error activating the account.", "danger");
        }
    }



    /**
     * Show login form
     */
    public function loginForm() {
            $this->loadView('auth/login');
        }

        /**
         * Process login
         */
    public function login() {
        header('Content-Type: application/json');
        ob_clean();

        // Security: block after multiple failed attempts
        $ipKey = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
        $blockKey = 'login_block_' . $_SERVER['REMOTE_ADDR'];

        // If blocked, check time
        // If blocked, check if the time has already expired
        if (isset($_SESSION[$blockKey])) {
            if (time() < $_SESSION[$blockKey]) {
                $remaining = $_SESSION[$blockKey] - time();
                echo json_encode([
                    'success' => false,
                    'message' => "Too many failed attempts. Try again in $remaining seconds."
                ]);
                return;
            } else {
                // 💡 The block expired → variables are cleared and 5 new attempts are given
                unset($_SESSION[$blockKey]);
                unset($_SESSION[$ipKey]);
            }
        }


        $email = trim(Functions::sanitize($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (!$email || !$password) {
            Logger::warning("Failed login: empty fields.");
            echo json_encode(['success' => false, 'message' => 'Email and password are mandatory.']);            
            return;
        }



        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Logger::warning("Failed login: unregistered email -> $email");
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            return;
        }

        // Incorrect password
        if (!password_verify($password, $user['password'])) {
            $_SESSION[$ipKey] = ($_SESSION[$ipKey] ?? 0) + 1;

            if ($_SESSION[$ipKey] >= 5) {
                $_SESSION[$blockKey] = time() + 30; // Block for 30 seconds
                Logger::warning("Login blocked for 30s for IP {$_SERVER['REMOTE_ADDR']}");
                echo json_encode([
                    'success' => false,
                    'message' => "Too many failed attempts. Wait 30 seconds."
                ]);
                return;
            }

            Logger::warning("Failed login: incorrect password for $email");
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
            return;
        }

        if ((int)$user['is_active'] == 0) {
            Logger::info("Blocked login: inactive account -> $email");
            echo json_encode(['success' => false, 'message' => 'Your account has not been activated yet.']);
            return;
        }

        if ((int)$user['is_active'] == 2) {
            Logger::info("Blocked login: inactive account -> $email");
            echo json_encode(['success' => false, 'message' => 'Your account is blocked.']);
            return;
        }

        if ((int)$user['is_active'] == 3) {
            $closure = $userModel->getClosureInfo($user['id']);

            if ($closure) {
                $requestedAt = strtotime($closure['requested_at']);
                $now = time();
                $daysElapsed = ($now - $requestedAt) / (60 * 60 * 24);

                if ($daysElapsed < 30) {
                    // ✅ Less than 30 days: Revive account
                    $userModel->reactivateUser($user['id']);
                    Logger::info("User $email reactivated their account after $daysElapsed days.");
                    // Allow normal authentication to continue
                } else {
                    // ❌ More than 30 days, maintain block
                    Logger::info("Blocked login: account in permanent closure -> $email");
                    echo json_encode(['success' => false, 'message' => 'Your account has been closed.']);
                    return;
                }
            } else {
                // ❌ Closure request not found (rare)
                Logger::warning("Status 3 but no record in account_closures -> $email");
                echo json_encode(['success' => false, 'message' => 'Your account is not available.']);
                return;
            }
        }


        // ✅ Secure session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],         
        ];

        unset($_SESSION[$ipKey]);
        unset($_SESSION[$blockKey]);

        Logger::info("Successful login: $email");
        //$this->notificationModel->create($user['id'], "Welcome to the system","INFO");

        // After validating the user
        $pref = (new UserModel())->getByUserId($user['id']);
        $_SESSION['dark_mode'] = $pref['dark_mode'] ?? 0;


        echo json_encode(['success' => true, 'message' => 'Successful login.']);
    }


    public function logout() {
        session_start();        
        session_unset();      // Remove all session variables
        session_destroy();    // Destroy the session
        session_start();
        $this->setFlashAlert("Session closed successfully");
        // Redirect to login
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
            echo json_encode(['success' => false, 'message' => 'Email is mandatory.']);
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Logger::warning("Recovery attempt with unregistered email: $email");
            echo json_encode(['success' => false, 'message' => 'If the email is registered, you will receive instructions.']);
            return;
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token in database
        if ($userModel->storePasswordResetToken($user['id'], $token, $expiresAt)) {
            // Send email
            if (Mailer::sendPasswordResetEmail($email, $user['name'], $token)) {
                Logger::info("Recovery token generated and sent to $email");
                echo json_encode(['success' => true, 'message' => 'We have sent a link to your email to reset your password.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not send recovery email.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not generate recovery token.']);
        }
    }


    public function resetPasswordForm() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $this->setFlashAlert("Invalid token","danger");
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }

        $userModel = new UserModel();
        $record = $userModel->getPasswordResetByToken($token);

        // Check if the token exists and has not expired
        if (!$record || strtotime($record['expires_at']) < time()) {
            Logger::warning("Attempt to use invalid or expired token: $token");
            $this->setFlashAlert("The link to change your password has expired or is invalid.","info");
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
            echo json_encode(['success' => false, 'message' => 'All fields are mandatory.']);
            return;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            return;
        }

        $userModel = new UserModel();
        $resetEntry = $userModel->getPasswordResetByToken($token);

        if (!$resetEntry) {
            Logger::warning("Invalid token for reset: $token");
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token.']);
            return;
        }

        $userId = $resetEntry['user_id'];
        $user = $userModel->findById($userId); // ✅ NECESSARY

        // Change the password
        $success = $userModel->updatePassword($userId, $password);

        if ($success) {
            $userModel->deletePasswordReset($token); // Remove the token           
            // ✅ Send confirmation email
            Mailer::sendPasswordChangedEmail($user['email'], $user['name']);
            Logger::info("User changed their password: ID $userId");
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            Logger::error("Error updating password for user ID: $userId");
            echo json_encode(['success' => false, 'message' => 'Could not update the password.']);
        }
    }


}

?>

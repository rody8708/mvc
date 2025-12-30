<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use App\Core\Logger;

class UserModel extends Model {
    /**
     * Crear un nuevo usuario con token de activación
     */
    public function createUser($name, $email, $password, $token) {
        try {
            // Evitar duplicados por seguridad
            if ($this->emailExists($email)) {
                Logger::warning("Intento de duplicado en base de datos: $email");
                return false;
            }

            $stmt = self::getDb()->prepare("
                INSERT INTO users (name, email, password, activation_token)
                VALUES (:name, :email, :password, :token)
            ");

            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);

            if ($stmt->execute()) {
                Logger::info("✅ Usuario registrado: $email");                
                return true;
            } else {
                Logger::error("❌ Fallo al insertar usuario en la base de datos: $email");
                return false;
            }

        } catch (\PDOException $e) {
            Logger::error("💥 Error PDO al crear usuario $email: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Buscar usuario por token de activación
     */
    public function findByToken($token) {
        $stmt = self::getDb()->prepare("SELECT * FROM users WHERE activation_token = ? AND is_active = 0");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Activar cuenta por token
     */
    public function activateUser($token) {
        $stmt = self::getDb()->prepare("UPDATE users SET is_active = 1, activation_token = NULL WHERE activation_token = ?");
        return $stmt->execute([$token]);
    }

    /**
     * Verificar si el correo ya está registrado
     */
    public function emailExists($email) {
        $stmt = self::getDb()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function nameExists($name) {
        $stmt = self::getDb()->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function findByEmail($email) {
        $stmt = self::getDb()->prepare("
            SELECT users.*, roles.name AS role_name
            FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            WHERE users.email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = self::getDb()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllUsers() {
        $stmt = self::getDb()->query("
            SELECT 
                users.id, 
                users.name, 
                users.email, 
                users.is_active AS account_status_id, 
                roles.name AS role_name, 
                users.role_id,
                account_statuses.name AS account_status_name
            FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            LEFT JOIN account_statuses ON users.is_active = account_statuses.id
            ORDER BY users.name
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    public function getAllRoles() {
        $stmt = self::getDb()->query("SELECT id, name FROM roles ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserRole($userId, $roleId) {
        $stmt = self::getDb()->prepare("UPDATE users SET role_id = :role WHERE id = :id");
        return $stmt->execute([
            ':role' => $roleId,
            ':id' => $userId
        ]);
    }

    public function getRoleNameById($id) {
        $stmt = self::getDb()->prepare("SELECT name FROM roles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn();
    }

    public function deleteById($id) {
        $stmt = self::getDb()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createUserByAdmin($name, $email, $password, $roleId, $isActive, $token = null) {
        try {
            $stmt = self::getDb()->prepare("
                INSERT INTO users (name, email, password, is_active, role_id, activation_token)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $isActive,
                $roleId,
                $token
            ]);
            Logger::info("Usuario creado desde admin: $email");            
            return true;
        } catch (\PDOException $e) {
            Logger::error("Error al crear usuario desde admin: " . $e->getMessage());
            return false;
        }
    }


    public function updateUser($id, $name, $email, $password, $roleId, $isActive) {
        try {
            $query = "UPDATE users SET name = ?, email = ?, role_id = ?, is_active = ?";
            $params = [$name, $email, $roleId, $isActive];

            if (!empty($password)) {
                $query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $query .= " WHERE id = ?";
            $params[] = $id;

            $stmt = self::getDb()->prepare($query);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            Logger::error("Error al actualizar usuario ID $id: " . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertId() {
        return self::getDb()->lastInsertId();
    }

    public function storePasswordResetToken($userId, $token, $expiresAt) {
        try {

            $del = self::getDb()->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->execute([$userId]);

            $stmt = self::getDb()->prepare("
                INSERT INTO password_resets (user_id, token, expires_at)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$userId, $token, $expiresAt]);
        } catch (\PDOException $e) {
            Logger::error("Error al guardar token de recuperación: " . $e->getMessage());
            return false;
        }
    }

    public function getPasswordResetByToken($token) {
        $stmt = self::getDb()->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function deletePasswordReset($token) {
        $stmt = self::getDb()->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }

    public function updatePassword($userId, $newPassword) {
        try {
            // Actualizar la contraseña en la tabla users
            $stmt = self::getDb()->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);

            // Eliminar el token de password_resets
            $del = self::getDb()->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $del->execute([$userId]);
            
            return true;
        } catch (\PDOException $e) {
            Logger::error("Error al actualizar contraseña para usuario ID: $userId - " . $e->getMessage());
            return false;
        }
    }


    public function getById($id) {
        $stmt = self::getDb()->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $name, $email) {
        try {
            $stmt = self::getDb()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $id]);
        } catch (\PDOException $e) {
            Logger::error("Error al actualizar perfil del usuario ID $id: " . $e->getMessage());
            return false;
        }
    }

    public function updatePasswordProfile($id, $newPassword) {
        try {
            $stmt = self::getDb()->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([
                password_hash($newPassword, PASSWORD_DEFAULT),
                $id
            ]);
        } catch (\PDOException $e) {
            Logger::error("Error al cambiar la contraseña del usuario ID $id: " . $e->getMessage());
            return false;
        }
    }


    public function getByUserId($userId) {
        $stmt = self::getDb()->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setDarkMode($userId, $enabled) {
        $enabled = $enabled ? 1 : 0; // 🔥 Fuerza que siempre sea 1 o 0
        $stmt = self::getDb()->prepare("UPDATE user_preferences SET dark_mode = ? WHERE user_id = ?");
        return $stmt->execute([$enabled, $userId]);
    }


    public function setLanguage($userId, $language) {
        $stmt = self::getDb()->prepare("UPDATE user_preferences SET language = ? WHERE user_id = ?");
        return $stmt->execute([$language, $userId]);
    }




    public function createUserPreferences($userId) {
        $stmt = self::getDb()->prepare("
            INSERT INTO user_preferences (user_id, dark_mode, language)
            VALUES (?, 0, 'en')
        ");
        return $stmt->execute([$userId]);
    }






    public function saveAvatarFile($userId, $file) {
    $stmt = self::getDb()->prepare("
        INSERT INTO user_files (
            user_id,
            file_name,
            file_extension,
            file_size,
            file_type,
            content_purpose,
            file_path,
            uploaded_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    if ($stmt->execute([
        $userId,
        $file['name'],
        $file['ext'],
        $file['size'],
        $file['type'],
        'avatar',
        $file['relative_path']
    ])) {
        return self::getDb()->lastInsertId(); // ✅ Devuelve el ID insertado
    }

        return false; // ❌ Si falla
    }

    public function deleteAvatarRecord($userId) {
        $stmt = self::getDb()->prepare("DELETE FROM user_files WHERE user_id = ? AND content_purpose = 'avatar'");
        return $stmt->execute([$userId]);
    }

    public function updateAvatarFileName($avatarId, $newFileName, $newRelativePath) {
        $stmt = self::getDb()->prepare("
            UPDATE user_files
            SET file_name = ?, file_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([$newFileName, $newRelativePath, $avatarId]);
    }



    public function getUserLogs($userName) {
        $stmt = self::getDb()->prepare("
            SELECT * FROM logs 
            WHERE user = ? 
            AND (
                action LIKE '%inició sesión%' OR
                action LIKE '%cambió su contraseña%' OR
                action LIKE '%actualizó su perfil%' OR
                action LIKE '%actualizó su foto de perfil%' OR
                action LIKE '%modificó sus preferencias%' OR
                action LIKE '%solicitó eliminación%'
            )
            ORDER BY timestamp DESC 
            LIMIT 20
        ");
        $stmt->execute([$userName]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function setUserInactive($userId, $state = 3) {
        $stmt = self::getDb()->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$state, $userId]);
    }

    public function requestAccountClosure($userId) {
        $stmt = self::getDb()->prepare("INSERT INTO account_closures (user_id) VALUES (?)");
        return $stmt->execute([$userId]);
    }




    public function getClosureInfo($userId) {
    $stmt = self::getDb()->prepare("SELECT * FROM account_closures WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function reactivateUser($userId) {
        // Elimina el registro de solicitud de cierre si quieres (opcional)
        $stmt1 = self::getDb()->prepare("DELETE FROM account_closures WHERE user_id = ?");
        $stmt1->execute([$userId]);

        // Cambiar estado a activo
        $stmt2 = self::getDb()->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        return $stmt2->execute([$userId]);
    }


    public function getAllAccountStatuses() {
        $stmt = self::getDb()->query("SELECT id, name FROM account_statuses ORDER BY id");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



}

?>
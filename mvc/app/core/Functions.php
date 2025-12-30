<?php

namespace App\Core;

use App\Core\Logger;

class Functions {
    
    public static $forcedUser = null;
    public static $forcedOS = null;
    public static $forcedBrowser = null;

    /**
     * Copiar un archivo
     */
    public static function copyFile($source, $destination) {
        if (!file_exists($source)) {
            Logger::error("No se puede copiar. Archivo origen no encontrado: $source");
            return false;
        }

        if (!copy($source, $destination)) {
            Logger::error("Fallo al copiar de $source a $destination");
            return false;
        }

        Logger::info("Archivo copiado exitosamente de $source a $destination");
        return true;
    }

    /**
     * Mover un archivo
     */
    public static function moveFile($source, $destination) {
        if (!file_exists($source)) {
            Logger::error("No se puede mover. Archivo origen no encontrado: $source");
            return false;
        }

        if (!rename($source, $destination)) {
            Logger::error("Fallo al mover archivo de $source a $destination");
            return false;
        }

        Logger::info("Archivo movido de $source a $destination");
        return true;
    }

    /**
     * Eliminar un archivo
     */
    public static function deleteFile($filePath) {
        if (!file_exists($filePath)) {
            Logger::warning("Intento de eliminar archivo inexistente: $filePath");
            return false;
        }

        if (!unlink($filePath)) {
            Logger::error("No se pudo eliminar el archivo: $filePath");
            return false;
        }

        Logger::info("Archivo eliminado: $filePath");
        return true;
    }

    /**
     * Eliminar una carpeta y todo su contenido
     */
    public static function deleteDirectory($dirPath) {
        if (!is_dir($dirPath)) {
            Logger::warning("Intento de eliminar directorio inexistente: $dirPath");
            return false;
        }

        $items = scandir($dirPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $dirPath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                self::deleteDirectory($fullPath); // Recursivo
            } else {
                if (!unlink($fullPath)) {
                    Logger::error("No se pudo eliminar el archivo dentro de directorio: $fullPath");
                }
            }
        }

        if (!rmdir($dirPath)) {
            Logger::error("No se pudo eliminar el directorio: $dirPath");
            return false;
        }

        Logger::info("Directorio eliminado correctamente: $dirPath");
        return true;
    }

    /**
     * Método de prueba
     */
    public static function sayHello() {
        return "Hola desde Functions";
    }

    /**
     * Obtener la IP del cliente
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
    }

    /**
     * Obtener el navegador del cliente
     */
    public static function getBrowser() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser = "Desconocido";

        if (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/') !== false) {
            $browser = 'Opera';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/7') !== false) {
            $browser = 'Internet Explorer';
        }

        return $browser;
    }

    /**
     * Obtener el sistema operativo del cliente
     */
    public static function getOS() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $os = "Desconocido";

        if (preg_match('/linux/i', $userAgent)) {
            $os = "Linux";
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = "Mac";
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $os = "Windows";
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = "Android";
        } elseif (preg_match('/iphone/i', $userAgent)) {
            $os = "iPhone";
        }

        return $os;
    }

  

    public static function getClientDetails() {

        // Si viene de la app móvil
        if (self::$forcedUser !== null || self::$forcedOS !== null) {
            return [
                'ip'        => self::getClientIP(),
                'navegador' => self::$forcedBrowser ?? 'mobile-app',
                'sistema'   => self::$forcedOS ?? 'unknown',
                'user_agent'=> 'mobile-app',
                'usuario'   => self::$forcedUser ?? 'unknown'
            ];
        }
    
        // Normal (web)
        $userData = self::getActiveUser();
        $usuario = $userData['name'] ?? $userData['email'] ?? 'Anonimo';
    
        return [
            'ip'        => self::getClientIP(),
            'navegador' => self::getBrowser(),
            'sistema'   => self::getOS(),
            'user_agent'=> $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido',
            'usuario'   => $usuario
        ];
    }





    /**
     * Verifica si hay una sesión de usuario activa
     * @return array|null - Retorna los datos del usuario o null si no hay sesión
     */
    public static function getActiveUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        return null;
    }

    /**
     * Obtener la URL actual completa
     */
    public static function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return $protocol . $host . $requestUri;
    }

    /**
     * Redirigir a otra URL
     */
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }

    /**
     * Sanitizar entrada del usuario para evitar XSS
     */
    public static function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar token aleatorio (ideal para CSRF o sesiones)
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Formatear bytes (B, KB, MB, GB, etc.)
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $power = ($bytes > 0) ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $bytes /= pow(1024, $power);

        return round($bytes, $precision) . ' ' . $units[$power];
    }


    public static function isLoggedIn() {
        if (!empty($_SESSION['expired'])) {
            unset($_SESSION['expired']);
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }
        return isset($_SESSION['user']);
    }

    
    public static function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

        return $_SESSION['csrf_token'];
    }


    public static function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }


    public static function addModuleToJson($name, $slug, $menu_label,) {
        $path = BASE_PATH . '/config/modules.json';
        $modules = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        
        // Evitar duplicados
        foreach ($modules as $m) {
            if ($m['slug'] === $slug) return;
        }

        $modules[] = ['name' => $name, 'slug' => $slug, 'menu_label' => $menu_label, 'admin_only' => false, 'auth_only' => true];
        file_put_contents($path, json_encode($modules, JSON_PRETTY_PRINT));
    }

    public static function removeModuleFromJson($slug) {
        $path = BASE_PATH . '/config/modules.json';
        if (!file_exists($path)) return;

        $modules = json_decode(file_get_contents($path), true);
        $modules = array_filter($modules, fn($m) => $m['slug'] !== $slug);
        file_put_contents($path, json_encode(array_values($modules), JSON_PRETTY_PRINT));
    }


    public static function uploadAvatar($file, $userId) {
        $maxSize = 3 * 1024 * 1024; // 3MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            Logger::error("Archivo no válido para avatar.");
            return ['success' => false, 'message' => 'Archivo no válido.'];
        }

        if ($file['size'] > $maxSize) {
            Logger::error("El archivo excede el tamaño máximo de 3MB.");
            return ['success' => false, 'message' => 'El archivo excede el tamaño máximo de 3MB.'];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            Logger::error("Formato de imagen no permitido: $mime");
            return ['success' => false, 'message' => 'Formato de imagen no permitido. Solo JPG y PNG.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uploadDir = "/public/assets/images/avatar/$userId";
        $absolutePath = BASE_PATH . $uploadDir;

        if (!is_dir($absolutePath)) mkdir($absolutePath, 0755, true);

        // ⬇️ Archivo temporal con nombre temporal
        $tempName = uniqid('tmp_', true) . '.' . $ext;
        $fullPath = "$absolutePath/$tempName";
        $relativePath = "images/avatar/$userId/$tempName";

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            Logger::info("Avatar temporal guardado correctamente: $tempName");

            return [
                'success' => true,
                'file' => [
                    'name' => $tempName,
                    'ext' => $ext,
                    'size' => $file['size'],
                    'type' => $mime,
                    'full_path' => $fullPath,
                    'relative_path' => $relativePath
                ]
            ];
        }

        Logger::error("No se pudo mover el archivo temporal a $fullPath");
        return ['success' => false, 'message' => 'No se pudo guardar la imagen.'];
    }



    public static function getUserAvatarUrl($userId) {
        $json = self::getUserJson($userId);
        $path = isset($json['avatar_path']) ? $json['avatar_path'] : 'images/default-avatar.jpeg';
        
        $absolutePath = BASE_PATH . '/public/assets/' . $path;

        if (file_exists($absolutePath)) {
            // ✅ El archivo existe, devolver su URL
            return BASE_URL . 'assets/' . $path;
        } else {
            // ❌ No existe, devolver avatar por defecto
            return BASE_URL . 'assets/images/default-avatar.jpeg';
        }
    }




    public static function saveUserJson(int $userId, array $newData): bool {
        try {
            $path = BASE_PATH . "/app/data/users/{$userId}.json";

            // Crear carpeta si no existe
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $existingData = [];

            // Leer datos previos si el archivo existe
            if (file_exists($path)) {
                $jsonContent = file_get_contents($path);
                $existingData = json_decode($jsonContent, true) ?? [];
            }

            // Mezclar datos nuevos con los existentes
            $mergedData = array_merge($existingData, $newData);

            // Guardar archivo actualizado
            file_put_contents($path, json_encode($mergedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return true;

        } catch (\Exception $e) {
            Logger::error("❌ Error al guardar el JSON del usuario $userId: " . $e->getMessage());
            return false;
        }
    }


    public static function updateUserField(int $userId, string $field, $value): bool {
        try {
            return self::saveUserJson($userId, [$field => $value]);
        } catch (\Exception $e) {
            Logger::error("❌ Error al actualizar el campo '$field' en JSON del usuario $userId: " . $e->getMessage());
            return false;
        }
    }



    public static function getUserJson(int $userId): array {
        $path = BASE_PATH . "/app/data/users/{$userId}.json";

        if (!file_exists($path)) {
            return []; // No existe = sin preferencias
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }


    public static function generateUserJson(int $userId): bool {
        $path = BASE_PATH . "/app/data/users/{$userId}.json";

        // Asegura que el directorio exista
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // Crear un JSON vacío (estructura vacía para el usuario)
        $emptyData = [];

        return file_put_contents($path, json_encode($emptyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }


    public static function deleteUserJson(int $userId): bool {
    $path = BASE_PATH . "/app/data/users/{$userId}.json";

    if (file_exists($path)) {
        return unlink($path); // Elimina el archivo y devuelve true si fue exitoso
    }

        return false; // No existía el archivo
    }


    public static function updateUserLanguageInJson($userId, $language) {
        $json = self::getUserJson($userId);
        $json['language'] = $language;
        return self::saveUserJson($userId, $json);
    }
    
    
    
    
    public static function formatUtcToLocal(
        ?string $utc,
        string $tz = 'America/New_York',
        string $format = 'Y-m-d H:i:s'
    ): string {
        if (empty($utc)) {
            return '';
        }

        try {
            $dtUtc = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
            $dtLocal = $dtUtc->setTimezone(new DateTimeZone($tz));
            return $dtLocal->format($format);
        } catch (\Throwable $e) {
            return (string)$utc; // fallback
        }
    }
  



}

?>

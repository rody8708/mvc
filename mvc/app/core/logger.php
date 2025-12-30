<?php

namespace App\Core;


class Logger {
    private static $logFile;

    public static function init($filePath = null) {
        self::$logFile = $filePath ?? BASE_PATH . '/app/logs/app.log';

        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        if (!file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, "=== LOG INICIADO ===\n", FILE_APPEND);
        }
    }

    public static function log($message, $level = 'INFO') {
        $date = date('Y-m-d H:i:s');

        $details = \App\Core\Functions::getClientDetails();
        $ip = $details['ip'];
        $browser = $details['navegador'];
        $os = $details['sistema'];
        $user = $details['usuario'];

        $formatted = "[$date] [$level] [$ip | $os | $browser | Usuario: $user] $message" . PHP_EOL;

        // Escribe en archivo siempre
        file_put_contents(self::$logFile, $formatted, FILE_APPEND);

        // Luego intenta guardar en base de datos
        try {
            self::logToDBInternal($user, $ip, $os, $browser, $message, $level, $date);
        } catch (\Throwable $e) {
            // Si falla, solo lo dejamos en archivo sin reintentar
            file_put_contents(self::$logFile, "[ERROR] No se pudo guardar en DB: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }


    public static function handleError($errno, $errstr, $errfile, $errline) {
        self::error("❗Error $errno: $errstr en $errfile:$errline");
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
            self::error("💥 Fatal: {$error['message']} en {$error['file']}:{$error['line']}");
        }
    }





    private static function logToDBInternal($user, $ip, $os, $browser, $action, $level, $timestamp) {
        $db = \App\Core\Model::getDb();

        $stmt = $db->prepare("
            INSERT INTO logs (user, ip, os, browser, action, level, timestamp)
            VALUES (:user, :ip, :os, :browser, :action, :level, :timestamp)
        ");

        $stmt->execute([
            ':user' => $user,
            ':ip' => $ip,
            ':os' => $os,
            ':browser' => $browser,
            ':action' => $action,
            ':level' => $level,
            ':timestamp' => $timestamp
        ]);
    }




    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function warning($message) {
        self::log($message, 'WARNING');
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
}


?>
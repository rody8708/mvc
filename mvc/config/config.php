<?php
// ⛑ Mostrar errores solo en desarrollo (cámbialo a 0 en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Zona horaria
date_default_timezone_set('America/New_York');

// Iniciar sesión
session_start();


// Duración máxima de inactividad (ejemplo: 900 segundos = 15 minutos)
define('MAX_INACTIVITY', 900);

// Verificar si hay inactividad
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > MAX_INACTIVITY) {
        // 🛑 Sesión expirada por inactividad
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['expired'] = true;
    }
}

// Actualizar el tiempo de última actividad
$_SESSION['last_activity'] = time();

// Definir rutas y constantes
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'https://mvc.zendrhax.com/');
define('DB_PATH', BASE_PATH . '/app/db/db.sqlite');
define('NOTIFICATION_MODE', 'websocket'); // opciones: 'polling' o 'websocket'


// ✅ Cargar Logger manualmente (antes del autoload)
require_once BASE_PATH . '/app/core/logger.php';
\App\Core\Logger::init();

// ✅ Capturar errores fatales
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    \App\Core\Logger::error("❗Error $errno: $errstr en $errfile:$errline");
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        \App\Core\Logger::error("💥 Fatal: {$error['message']} en {$error['file']}:{$error['line']}");
    }
});

// ✅ Cargar funciones auxiliares antes del autoload
require_once BASE_PATH . '/app/core/Functions.php';

// ✅ Cargar autoload
require_once BASE_PATH . '/app/core/autoload.php';

?>
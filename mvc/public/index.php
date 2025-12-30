<?php
// Cargar el archivo que gestiona toda la configuracion
$path = dirname(__DIR__) . '/config/config.php';
if (!file_exists($path)) {
    die("❌ config.php no encontrado: $path");
}
require_once $path;


// ==================================================
// Load .env file manually
// ==================================================
$envPath = dirname(__DIR__) . '/config/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}



// Carga las rutas de la aplicacion
require_once dirname(__DIR__) . '/routes/web.php';

?>
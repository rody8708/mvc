<?php

spl_autoload_register(function ($class) {
    // Quitar el prefijo App\
    $class = str_replace('App\\', '', $class);

    // Convertir carpetas a minúscula excepto el nombre del archivo (última parte)
    $parts = explode('\\', $class);
    $lastIndex = count($parts) - 1;

    foreach ($parts as $i => &$part) {
        if ($i !== $lastIndex) {
            $part = strtolower($part);
        }
    }

    $classPath = BASE_PATH . '/app/' . implode('/', $parts) . '.php';

    if (file_exists($classPath)) {
        require_once $classPath;
    } else {
        \App\Core\Logger::info("❌ No se pudo cargar la clase '$class'. Ruta generada: $classPath");
        die("Error: No se pudo cargar la clase '$class'. Verifica la ruta.");
    }
});


<?php

spl_autoload_register(function ($class) {
    // Remove the App\ prefix
    $class = str_replace('App\\', '', $class);

    // Convert folders to lowercase except for the file name (last part)
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
        \App\Core\Logger::info("❌ Could not load class '$class'. Generated path: $classPath");
        die("Error: Could not load class '$class'. Check the path.");
    }
});


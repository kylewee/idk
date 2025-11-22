<?php

/**
 * PSR-4 Autoloader
 *
 * Simple autoloader for the MechanicStAugustine namespace.
 * This will be replaced by Composer's autoloader in the future.
 */

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'MechanicStAugustine\\';

    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separators with directory separators
    // and append with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Also load non-namespaced classes
spl_autoload_register(function ($class) {
    // For backward compatibility, load classes from src/
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

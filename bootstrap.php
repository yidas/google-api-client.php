<?php

define('BASE_PATH', __DIR__);

// Composer
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
	die('Composer is not set, run `composer install` in the app root.');
}
require_once $autoloadPath;

/**
 * PSR-4 Autoload for app
 */
$prefix = 'app';
$basePath = BASE_PATH;
        
spl_autoload_register(function ($classname) use ($prefix, $basePath) {
    // Prefix check
    if (strpos(strtolower($classname), "{$prefix}\\")===0) {
        // Locate class relative path
        $classname = str_replace("{$prefix}\\", "", $classname);
        $filepath = realpath($basePath) . DIRECTORY_SEPARATOR .  str_replace('\\', DIRECTORY_SEPARATOR, ltrim($classname, '\\')) . '.php';
        
        if (file_exists($filepath)) {
            
            require $filepath;
        }
    }
});
<?php
// Here you can initialize variables that will be available to your tests
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Console/Kernel.php';
require_once __DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';

spl_autoload_register(
    function ($class) {
        if (file_exists(str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php')) {
            require_once str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
        }
    }
);

register_shutdown_function(function () {
    $files = glob('./tests/_output/*Test*.php');
    foreach ($files as $file) {
        unlink($file);
    }
});
<?php
// Here you can initialize variables that will be available to your tests
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/Console/Kernel.php';
require_once __DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';

spl_autoload_register(
    function($class)
    {
        if($class !== 'config')
        require_once str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
    }
);
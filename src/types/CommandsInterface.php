<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 17.12.16
 * Time: 15:38
 */

namespace rjapi\types;


interface CommandsInterface
{
    // laravel-module commands
    const LARAVEL_MODULE_MAKE = 'php artisan module:make';
    const LARAVEL_MODULE_USE = 'php artisan module:use';
    const LARAVEL_MODULE_LIST = 'php artisan module:list';
}
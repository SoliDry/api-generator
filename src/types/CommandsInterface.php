<?php

namespace SoliDry\Types;


interface CommandsInterface
{
    // laravel-module commands
    public const LARAVEL_MODULE_MAKE = 'php artisan module:make';
    public const LARAVEL_MODULE_USE  = 'php artisan module:use';
    public const LARAVEL_MODULE_LIST = 'php artisan module:list';
}
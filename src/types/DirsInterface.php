<?php

namespace rjapi\types;

interface DirsInterface
{
    // Laravel dirs
    public const CONFIG_DIR        = 'config';
    public const MODULE_CONFIG_DIR = 'Config';
    public const APPLICATION_DIR   = 'App';
    public const MODULES_DIR       = 'Modules';
    public const HTTP_DIR          = 'Http';
    public const CONTROLLERS_DIR   = 'Controllers';
    public const MIDDLEWARE_DIR    = 'Middleware';
    public const ENTITIES_DIR      = 'Entities';
    public const DATABASE_DIR      = 'Database';
    public const MIGRATIONS_DIR    = 'Migrations';
    // directory to store raml history
    public const GEN_DIR       = '.gen';
    public const EXCLUDED_DIRS = ['.', '..'];
}
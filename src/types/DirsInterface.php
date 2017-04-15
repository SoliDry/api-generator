<?php
namespace rjapi\types;

interface DirsInterface
{
    // Laravel dirs
    const CONFIG_DIR        = 'config';
    const MODULE_CONFIG_DIR = 'Config';
    const APPLICATION_DIR   = 'App';
    const MODULES_DIR       = 'Modules';
    const HTTP_DIR          = 'Http';
    const CONTROLLERS_DIR   = 'Controllers';
    const MIDDLEWARE_DIR    = 'Middleware';
    const ENTITIES_DIR      = 'Entities';
    const DATABASE_DIR      = 'Database';
    const MIGRATIONS_DIR    = 'Migrations';
}
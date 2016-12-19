<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 09/12/2016
 * Time: 11:45
 */

namespace rjapi\blocks;

interface ModelsInterface
{
    // Laravel
    const LARAVEL_ACTIVE_RECORD  = 'Model';
    const LARAVEL_PROPERTY_TABLE = 'table';
    const DEFAULT_LIMIT          = 20;

    // Methods
    const MODEL_METHOD_ALL = 'all()';
}
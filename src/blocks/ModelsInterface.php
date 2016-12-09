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
    // Yii
    const YII_ACTIVE_RECORD     = 'ActiveRecord';
    const YII_METHOD_TABLE_NAME = 'tableName';

    // Laravel
    const LARAVEL_ACTIVE_RECORD = 'Eloquent';
    const LARAVEL_METHOD_TABLE = 'table';
}
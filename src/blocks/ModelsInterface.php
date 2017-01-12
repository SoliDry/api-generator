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
    const LARAVEL_ACTIVE_RECORD   = 'Model';
    const LARAVEL_MIGRATION_CLASS = 'Migration';
    const LARAVEL_PROPERTY_TABLE  = 'table';
    const DEFAULT_LIMIT           = 20;
    const DEFAULT_PAGE            = 1;
    const PARAM_PAGE              = 'page';
    const PARAM_LIMIT             = 'limit';
    const LARAVEL_FILTER_ENUM     = 'in';
    const LARAVEL_FILTER_REGEX    = 'regex';
    const LARAVEL_FILTER_MIN      = 'min';
    const LARAVEL_FILTER_MAX      = 'max';

    // Methods
    const MODEL_METHOD_ALL      = 'all';
    const MODEL_METHOD_WHERE    = 'where';
    const MODEL_METHOD_ORDER_BY = 'orderBy';
    // ONE TO ONE
    const MODEL_METHOD_HAS_ONE = 'hasOne';
    // ONE TO MANY
    const MODEL_METHOD_HAS_MANY = 'hasMany';
    // MANY TO ONE INVERSE
    conSt MODEL_METHOD_BELONGS_TO = 'belongsTo';
    // MANY TO MANY TWO WAY
    const MODEL_METHOD_BELONGS_TO_MANY = 'belongsToMany';
    const MODEL_METHOD_RELATIONS       = 'relations';

    // SQL
    const SQL_DESC = 'desc';
    const SQL_ASC  = 'asc';

    // Migrations
    const MIGRATION_SCHEMA = 'Schema';
    const MIGRATION_CREATE = 'create';
    const MIGRATION_TABLE  = 'table';

    // Migration methods
    const MIGRATION_METHOD_INCREMENTS     = 'increments';
    const MIGRATION_METHOD_BIG_INCREMENTS = 'bigIncrements';
    const MIGRATION_METHOD_STRING         = 'string';
    const MIGRATION_METHOD_INTEGER        = 'integer';
    const MIGRATION_METHOD_TINYINT        = 'unsignedTinyInteger';
    const MIGRATION_METHOD_TIMESTAMPS     = 'timestamps';
    const MIGRATION_METHOD_DATETIME       = 'dateTime';
    const MIGRATION_METHOD_ENUM           = 'enum';
    const MIGRATION_METHOD_DROP           = 'dropIfExists';
    const MIGRATION_METHOD_UP             = 'up';
    const MIGRATION_METHOD_DOWN           = 'down';

    // base properties
    const PROPERTY_TABLE       = 'table';
    const PROPERTY_PRIMARY_KEY = 'primaryKey';
    const PROPERTY_TIMESTAMPS  = 'timestamps';

    const ID_MAX_INCREMENTS = 10;
}
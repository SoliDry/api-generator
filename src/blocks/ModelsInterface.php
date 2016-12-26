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
    const LARAVEL_FILTER_ENUM     = 'in';
    const LARAVEL_FILTER_REGEX    = 'regex';
    const LARAVEL_FILTER_MIN      = 'min';
    const LARAVEL_FILTER_MAX      = 'max';

    // Methods
    const MODEL_METHOD_ALL      = 'all';
    const MODEL_METHOD_WHERE    = 'where';
    const MODEL_METHOD_ORDER_BY = 'orderBy';

    // SQL
    const SQL_DESC = 'desc';
    const SQL_ASC  = 'asc';

    // Migrations
    const MIGRATION_SCHEMA = 'Schema';
    const MIGRATION_CREATE = 'create';
    const MIGRATION_TABLE  = 'table';

    const MIGRATION_METHOD_INCREMENTS = 'increments';
    const MIGRATION_METHOD_STRING     = 'string';
    const MIGRATION_METHOD_INTEGER    = 'integer';
    const MIGRATION_METHOD_TINYINT    = 'unsignedTinyInteger';
    const MIGRATION_METHOD_TIMESTAMPS = 'timestamps';
    const MIGRATION_METHOD_DATETIME   = 'dateTime';
    const MIGRATION_METHOD_ENUM       = 'enum';
    const MIGRATION_METHOD_DROP       = 'dropIfExists';
    const MIGRATION_METHOD_UP         = 'up';
    const MIGRATION_METHOD_DOWN       = 'down';
}
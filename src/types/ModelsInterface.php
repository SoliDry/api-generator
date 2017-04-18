<?php
namespace rjapi\types;

interface ModelsInterface
{
    const ID = 'id';
    // Laravel
    const LARAVEL_ACTIVE_RECORD   = 'Model';
    const LARAVEL_MIGRATION_CLASS = 'Migration';
    const LARAVEL_PROPERTY_TABLE  = 'table';
    const DEFAULT_LIMIT           = 20;
    const DEFAULT_PAGE            = 1;
    const DEFAULT_SORT            = self::SQL_DESC;
    const DEFAULT_DATA            = ['*']; // means get all fields
    const PARAM_PAGE              = 'page';
    const PARAM_LIMIT             = 'limit';
    const PARAM_SORT              = 'sort';
    const PARAM_DATA              = 'data';
    const PARAM_ORDER_BY          = 'order_by';
    const PARAM_FILTER            = 'filter';
    const LARAVEL_FILTER_ENUM     = 'in';
    const LARAVEL_FILTER_REGEX    = 'regex';
    const LARAVEL_FILTER_MIN      = 'min';
    const LARAVEL_FILTER_MAX      = 'max';
    const COLUMN                  = 'column';
    const DIRECTION               = 'direction';
    const PARENT_ID               = 'parent_id';

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
    // integer
    const MIGRATION_METHOD_TINY_INTEGER   = 'tinyInteger';
    const MIGRATION_METHOD_SMALL_INTEGER  = 'smallInteger';
    const MIGRATION_METHOD_MEDIUM_INTEGER = 'mediumInteger';
    const MIGRATION_METHOD_INTEGER        = 'integer';
    const MIGRATION_METHOD_BIG_INTEGER    = 'bigInteger';
    const MIGRATION_METHOD_UTINYINT       = 'unsignedTinyInteger';
    const MIGRATION_METHOD_USMALLINT      = 'unsignedSmallInteger';
    const MIGRATION_METHOD_UMEDIUMINT     = 'unsignedMediumInteger';
    const MIGRATION_METHOD_UINT           = 'unsignedInteger';
    const MIGRATION_METHOD_UBIGINT        = 'unsignedBigInteger';

    const INT_DIGITS_TINY   = 3;
    const INT_DIGITS_SMALL  = 5;
    const INT_DIGITS_MEDIUM = 8;
    const INT_DIGITS_INT    = 10;
    const INT_DIGITS_BIGINT = 20;

    // double
    const MIGRATION_METHOD_DOUBLE         = 'double';
    const MIGRATION_METHOD_FLOAT          = 'float';
    const MIGRATION_METHOD_TIMESTAMPS     = 'timestamps';
    const MIGRATION_METHOD_DATETIME       = 'dateTime';
    const MIGRATION_METHOD_DATE           = 'date';
    const MIGRATION_METHOD_TIME           = 'time';
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
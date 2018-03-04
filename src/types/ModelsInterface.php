<?php
namespace rjapi\types;

interface ModelsInterface
{
    public const ID = 'id';
    // Laravel
    public const LARAVEL_ACTIVE_RECORD   = 'Model';
    public const LARAVEL_MIGRATION_CLASS = 'Migration';
    public const LARAVEL_PROPERTY_TABLE  = 'table';
    public const DEFAULT_LIMIT           = 20;
    public const DEFAULT_PAGE            = 1;
    public const DEFAULT_SORT            = self::SQL_DESC;
    public const DEFAULT_DATA            = ['*']; // means get all fields
    public const PARAM_PAGE              = 'page';
    public const PARAM_LIMIT             = 'limit';
    public const PARAM_SORT              = 'sort';
    public const PARAM_DATA              = 'data';
    public const PARAM_ORDER_BY          = 'order_by';
    public const PARAM_FILTER            = 'filter';
    public const LARAVEL_FILTER_ENUM     = 'in';
    public const LARAVEL_FILTER_REGEX    = 'regex';
    public const LARAVEL_FILTER_MIN      = 'min';
    public const LARAVEL_FILTER_MAX      = 'max';
    public const COLUMN                  = 'column';
    public const DIRECTION               = 'direction';
    public const PARENT_ID               = 'parent_id';

    // Methods
    public const MODEL_METHOD_ALL      = 'all';
    public const MODEL_METHOD_WHERE    = 'where';
    public const MODEL_METHOD_ORDER_BY = 'orderBy';
    // ONE TO ONE
    public const MODEL_METHOD_HAS_ONE = 'hasOne';
    // ONE TO MANY
    public const MODEL_METHOD_HAS_MANY = 'hasMany';
    // MANY TO ONE INVERSE
    public const MODEL_METHOD_BELONGS_TO = 'belongsTo';
    // MANY TO MANY TWO WAY
    public const MODEL_METHOD_BELONGS_TO_MANY = 'belongsToMany';
    public const MODEL_METHOD_RELATIONS       = 'relations';

    // SQL
    public const SQL_DESC = 'desc';
    public const SQL_ASC  = 'asc';

    // Migrations
    public const MIGRATION_SCHEMA           = 'Schema';
    public const MIGRATION_CREATE           = 'create';
    public const MIGRATION_TABLE            = 'table';
    public const MIGRATION_TABLE_PTTRN      = '{table}';
    public const MIGRATION_COLUMN_PTTRN     = '{column}';
    public const MIGRATION_ADD_COLUMN       = 'add_column_' . self::MIGRATION_COLUMN_PTTRN . '_to_' . self::MIGRATION_TABLE_PTTRN;
    public const MIGRATION_ADD_COLUMN_CLASS = 'AddColumn' . self::MIGRATION_COLUMN_PTTRN . 'To' . self::MIGRATION_TABLE_PTTRN;

    // Migration methods
    public const MIGRATION_METHOD_INCREMENTS     = 'increments';
    public const MIGRATION_METHOD_BIG_INCREMENTS = 'bigIncrements';
    public const MIGRATION_METHOD_STRING         = 'string';
    // integer
    public const MIGRATION_METHOD_TINY_INTEGER   = 'tinyInteger';
    public const MIGRATION_METHOD_SMALL_INTEGER  = 'smallInteger';
    public const MIGRATION_METHOD_MEDIUM_INTEGER = 'mediumInteger';
    public const MIGRATION_METHOD_INTEGER        = 'integer';
    public const MIGRATION_METHOD_BIG_INTEGER    = 'bigInteger';
    public const MIGRATION_METHOD_UTINYINT       = 'unsignedTinyInteger';
    public const MIGRATION_METHOD_USMALLINT      = 'unsignedSmallInteger';
    public const MIGRATION_METHOD_UMEDIUMINT     = 'unsignedMediumInteger';
    public const MIGRATION_METHOD_UINT           = 'unsignedInteger';
    public const MIGRATION_METHOD_UBIGINT        = 'unsignedBigInteger';

    public const INT_DIGITS_TINY   = 3;
    public const INT_DIGITS_SMALL  = 5;
    public const INT_DIGITS_MEDIUM = 8;
    public const INT_DIGITS_INT    = 10;
    public const INT_DIGITS_BIGINT = 20;

    // double
    public const MIGRATION_METHOD_DOUBLE         = 'double';
    public const MIGRATION_METHOD_FLOAT          = 'float';
    public const MIGRATION_METHOD_TIMESTAMPS     = 'timestamps';
    public const MIGRATION_METHOD_DATETIME       = 'dateTime';
    public const MIGRATION_METHOD_DATE           = 'date';
    public const MIGRATION_METHOD_TIME           = 'time';
    public const MIGRATION_METHOD_ENUM           = 'enum';
    public const MIGRATION_METHOD_DROP           = 'dropIfExists';
    public const MIGRATION_METHOD_UP             = 'up';
    public const MIGRATION_METHOD_DOWN           = 'down';
    public const MIGRATION_DROP_COLUMN           = 'dropColumn';

    // base properties
    public const PROPERTY_TABLE       = 'table';
    public const PROPERTY_PRIMARY_KEY = 'primaryKey';
    public const PROPERTY_TIMESTAMPS  = 'timestamps';

    public const ID_MAX_INCREMENTS = 10;

    // db indices
    public const INDEX_TYPE_INDEX   = 'index';
    public const INDEX_TYPE_UNIQUE  = 'unique';
    public const INDEX_TYPE_PRIMARY = 'primary';
    public const INDEX_TYPE_FOREIGN = 'foreign';
    public const INDEX_COLUMN       = '_column';
    public const INDEX_REFERENCES   = 'references';
    public const INDEX_ON           = 'on';
    public const INDEX_ON_DELETE    = 'onDelete';
    public const INDEX_ON_UPDATE    = 'onUpdate';
}
<?php

namespace rjapi\types;

interface CustomsInterface
{
    public const CUSTOM_TYPES_ID                = 'ID';
    public const CUSTOM_TYPES_TYPE              = 'Type';
    public const CUSTOM_RELATIONSHIPS_DATA_ITEM = 'RelationshipsDataItem';

    public const CUSTOM_TYPES_RELATIONSHIPS = 'Relationships';
    public const CUSTOM_TYPES_QUERY_PARAMS  = 'QueryParams';
    public const CUSTOM_TYPES_FILTER        = 'Filter';
    public const CUSTOM_TYPES_ATTRIBUTES    = 'Attributes';
    public const CUSTOM_TYPES_TREES         = 'Trees';
    public const CUSTOM_PROP_JWT            = 'jwt';
    public const CUSTOM_TYPE_REDIS          = 'Redis';
}
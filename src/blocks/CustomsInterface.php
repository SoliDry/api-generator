<?php
namespace rjapi\blocks;

interface CustomsInterface
{
    const CUSTOM_TYPES_ID                          = 'ID';
    const CUSTOM_TYPES_TYPE                        = 'Type';
    const CUSTOM_TYPES_RELATIONSHIPS               = 'Relationships';
    const CUSTOM_TYPES_QUERY_SEARCH                = 'QuerySearch';
    const CUSTOM_TYPES_FILTER                      = 'Filter';
    const CUSTOM_TYPES_ATTRIBUTES                  = 'Attributes';
    const CUSTOM_TYPES_SINGLE_DATA_RELATIONSHIPS   = 'SinglDataRelationships';
    const CUSTOM_TYPES_MULTIPLE_DATA_RELATIONSHIPS = 'MultipleDataRelationships';
}
<?php
namespace rjapi\types;

interface RamlInterface
{
    // RAML types map to FW
    const RAML_TYPE_ARRAY    = 'array';
    const RAML_TYPE_OBJECT   = 'object';
    const RAML_TYPE_DATETIME = 'date';
    const RAML_TYPE_BOOLEAN  = 'boolean';
    const RAML_TYPE_STRING   = 'string';
    const RAML_TYPE_INTEGER  = 'integer';
    const RAML_TYPE_NUMBER   = 'number';

    const RAML_TYPE_FORMAT        = 'format';
    const RAML_TYPE_FORMAT_FLOAT  = 'float';
    const RAML_TYPE_FORMAT_DOUBLE = 'double';

    const RAML_PROPS         = 'properties';
    const RAML_ATTRS         = 'attributes';
    const RAML_RELATIONSHIPS = 'relationships';
    const RAML_TYPE          = 'type';
    const RAML_ID            = 'id';
    const RAML_DATA          = 'data';
    const RAML_ITEMS         = 'items';

    // RAML keys
    const RAML_KEY_REQUIRED      = 'required';
    const RAML_KEY_DESCRIPTION   = 'description';
    const RAML_KEY_DEFAULT       = 'default';
    const RAML_KEY_TYPES         = 'types';
    const RAML_KEY_USES          = 'uses';

    // RAML filters
    const RAML_STRING_MIN  = 'minLength';
    const RAML_STRING_MAX  = 'maxLength';
    const RAML_INTEGER_MIN = 'minimum';
    const RAML_INTEGER_MAX = 'maximum';
    const RAML_PATTERN     = 'pattern';
    const RAML_ENUM        = 'enum';
    const RAML_DATE        = 'date-only';
    const RAML_TIME        = 'time-only';
}
<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 02/11/2016
 * Time: 18:08
 */

namespace rjapi\blocks;

interface RamlInterface
{
    // RAML types
    const RAML_TYPE_ARRAY    = 'array';
    const RAML_TYPE_OBJECT   = 'object';
    const RAML_PROPS         = 'properties';
    const RAML_ATTRS         = 'attributes';
    const RAML_RELATIONSHIPS = 'relationships';
    const RAML_TYPE          = 'type';
    const RAML_ID            = 'id';
    const RAML_DATA          = 'data';
    const RAML_ITEMS         = 'items';
    const RAML_REQUIRED      = 'required';
    const RAML_DESCRIPTION   = 'description';

    // RAML filters
    const RAML_STRING_MIN  = 'minLength';
    const RAML_STRING_MAX  = 'maxLength';
    const RAML_INTEGER_MIN = 'minimum';
    const RAML_INTEGER_MAX = 'maximum';
    const RAML_PATTERN     = 'pattern';
    const RAML_ENUM        = 'enum';
}
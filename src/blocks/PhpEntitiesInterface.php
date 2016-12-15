<?php

namespace rjapi\blocks;

interface PhpEntitiesInterface
{
    const PHP_OPEN_TAG  = '<?php';
    const PHP_EXT       = '.php';
    const PHP_EXTENDS   = 'extends';
    const PHP_NAMESPACE = 'namespace';
    const PHP_CLASS     = 'class';
    const PHP_USE       = 'use';
    const PHP_FUNCTION  = 'function';
    const PHP_RETURN    = 'return';

    const OPEN_BRACE        = '{';
    const CLOSE_BRACE       = '}';
    const OPEN_BRACKET      = '[';
    const CLOSE_BRACKET     = ']';
    const OPEN_PARENTHESES  = '(';
    const CLOSE_PARENTHESES = ')';

    const TAB_PSR4     = '    ';
    const COMMA        = ',';
    const QUOTES       = '"';
    const COLON        = ':';
    const SEMICOLON    = ';';
    const DOLLAR_SIGN  = '$';
    const SLASH        = '/';
    const BACKSLASH    = '\\';
    const EQUALS       = '=';
    const SPACE        = ' ';
    const COMMENT      = '//';
    const DOUBLE_ARROW = '=>';
    const PIPE         = '|';

    const PHP_TYPES_ARRAY      = 'array';
    const PHP_TYPES_NULL       = 'null';
    const PHP_TYPES_STRING     = 'string';
    const PHP_TYPES_BOOL       = 'bool';
    const PHP_TYPES_BOOL_FALSE = 'false';
    const PHP_TYPES_BOOL_TRUE  = 'true';

    const PHP_MODIFIER_PUBLIC    = 'public';
    const PHP_MODIFIER_PRIVATE   = 'private';
    const PHP_MODIFIER_PROTECTED = 'protected';

    const PHP_STATIC = 'static';

    const PHP_RULES     = 'rules';
    const PHP_RELATIONS = 'relations';
    const PHP_AUTHORIZE = 'authorize';
}
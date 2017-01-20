<?php

namespace rjapi\types;

interface PhpInterface
{
    const PHP_OPEN_TAG  = '<?php';
    const PHP_EXT       = '.php';
    const PHP_EXTENDS   = 'extends';
    const PHP_NAMESPACE = 'namespace';
    const PHP_CLASS     = 'class';
    const PHP_USE       = 'use';
    const PHP_FUNCTION  = 'function';
    const PHP_RETURN    = 'return';
    const PHP_THIS      = 'this';

    const SYSTEM_UPDIR  = '../';
    const SYSTEM_CURDIR = './';

    const OPEN_BRACE        = '{';
    const CLOSE_BRACE       = '}';
    const OPEN_BRACKET      = '[';
    const CLOSE_BRACKET     = ']';
    const OPEN_PARENTHESES  = '(';
    const CLOSE_PARENTHESES = ')';

    const TAB_PSR4      = '    ';
    const COMMA         = ',';
    const DOT           = '.';
    const QUOTES        = '\'';
    const DOUBLE_QUOTES = '"';
    const COLON         = ':';
    const DOUBLE_COLON  = '::';
    const SEMICOLON     = ';';
    const DOLLAR_SIGN   = '$';
    const SLASH         = '/';
    const BACKSLASH     = '\\';
    const EQUALS        = '=';
    const SPACE         = ' ';
    const COMMENT       = '//';
    const ARROW         = '->';
    const DOUBLE_ARROW  = '=>';
    const PIPE          = '|';
    const AT            = '@';
    const DASH          = '-';
    const UNDERSCORE    = '_';
    const ASTERISK      = '*';
    const EXCLAMATION   = '!';

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

    const CLASS_CLOSURE = 'Closure';

    // php flow structs
    const IF = 'if';

    // key-words
    const ECHO = 'echo';
    const DIE  = 'die';
}
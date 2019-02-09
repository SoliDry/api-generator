<?php

namespace SoliDry\Types;

interface PhpInterface
{
    public const PHP_OPEN_TAG  = '<?php';
    public const PHP_EXT       = '.php';
    public const PHP_EXTENDS   = 'extends';
    public const PHP_NAMESPACE = 'namespace';
    public const PHP_CLASS     = 'class';
    public const PHP_USE       = 'use';
    public const PHP_FUNCTION  = 'function';
    public const PHP_RETURN    = 'return';
    public const PHP_THIS      = 'this';
    public const PHP_REQUIRE   = 'require';

    public const PHP_CONST_DIR = '__DIR__';

    public const SYSTEM_UPDIR  = '../';
    public const SYSTEM_CURDIR = './';

    public const OPEN_BRACE        = '{';
    public const CLOSE_BRACE       = '}';
    public const OPEN_BRACKET      = '[';
    public const CLOSE_BRACKET     = ']';
    public const OPEN_PARENTHESES  = '(';
    public const CLOSE_PARENTHESES = ')';

    public const TAB_PSR4          = '    ';
    public const COMMA             = ',';
    public const DOT               = '.';
    public const QUOTES            = '\'';
    public const DOUBLE_QUOTES     = '"';
    public const DOUBLE_QUOTES_ESC = '\"';
    public const COLON             = ':';
    public const DOUBLE_COLON      = '::';
    public const SEMICOLON         = ';';
    public const DOLLAR_SIGN       = '$';
    public const SLASH             = '/';
    public const BACKSLASH         = '\\';
    public const EQUALS            = '=';
    public const SPACE             = ' ';
    public const COMMENT           = '//';
    public const ARROW             = '->';
    public const DOUBLE_ARROW      = '=>';
    public const PIPE              = '|';
    public const AT                = '@';
    public const DASH              = '-';
    public const UNDERSCORE        = '_';
    public const ASTERISK          = '*';
    public const EXCLAMATION       = '!';

    public const PHP_TYPES_ARRAY      = 'array';
    public const PHP_TYPES_NULL       = 'null';
    public const PHP_TYPES_STRING     = 'string';
    public const PHP_TYPES_BOOL       = 'bool';
    public const PHP_TYPES_INT        = 'int';
    public const PHP_TYPES_BOOL_FALSE = 'false';
    public const PHP_TYPES_BOOL_TRUE  = 'true';

    public const PHP_MODIFIER_PUBLIC    = 'public';
    public const PHP_MODIFIER_PRIVATE   = 'private';
    public const PHP_MODIFIER_PROTECTED = 'protected';

    public const PHP_STATIC = 'static';

    public const PHP_RULES     = 'rules';
    public const PHP_RELATIONS = 'relations';
    public const PHP_AUTHORIZE = 'authorize';

    public const CLASS_CLOSURE = 'Closure';

    // php flow structs
    public const IF            = 'if';

    // key-words
    public const ECHO          = 'echo';
    public const DIE           = 'die';

    public const PHP_EXTENSION_PSPELL = 'pspell';
    public const ENCODING_UTF8        = 'utf-8';
}
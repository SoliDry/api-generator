<?php

namespace SoliDry\Types;

interface ConfigInterface
{
    public const QUERY_PARAMS     = 'query_params';
    public const TREES            = 'trees';
    public const ENABLED          = 'enabled';
    public const HIDE_MASK        = 'hide_mask';
    public const FLAGS            = 'flags';
    public const ACTIVATE         = 'activate';
    public const EXPIRES          = 'expires';
    public const SPELL_LANGUAGE   = 'spell_language';
    public const LANGUAGE         = 'language';
    public const DEFAULT_LANGUAGE = 'en';
    public const STATES           = 'states';
    public const INITIAL          = 'initial';
    public const CUSTOM_SQL       = 'custom_sql';
    public const QUERY            = 'query';
    public const BINDINGS         = 'bindings';
    public const ATTRIBUTES_CASE  = 'attributes_case';

    public const STATE_MACHINE = 'state_machine';
    public const SPELL_CHECK   = 'spell_check';
    public const BIT_MASK      = 'bit_mask';
    public const CACHE         = 'cache';
    public const JWT           = 'jwt';

    // cache entity settings
    public const CACHE_STAMPEDE_XFETCH = 'stampede_xfetch';
    public const CACHE_STAMPEDE_BETA   = 'stampede_beta';
    public const CACHE_TTL             = 'ttl';

    public const STATE_MACHINE_METHOD = 'setFsmOptions';
    public const SPELL_CHECK_METHOD   = 'setSpellOptions';
    public const BIT_MASK_METHOD      = 'setBitMaskOptions';
    public const CACHE_METHOD         = 'setCacheOptions';

    // json-api attributes can be in one of: camel-case, snake-case, lisp-case
    public const DEFAULT_CASE  = 'snake-case';
    public const CAMEL_CASE = 'camel-case';
    public const LISP_CASE = 'lisp-case';

    // todo: make this prop set via config for tests to run normally
    public const DEFAULT_ACTIVATE = 30;
    public const DEFAULT_EXPIRES  = 3600;
}
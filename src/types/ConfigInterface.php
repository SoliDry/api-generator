<?php

namespace rjapi\types;

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
    public const DATABASE         = 'database';
    public const PASSWORD         = 'password';
    public const HOST             = 'host';
    public const PORT             = 'port';

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
    public const JWT_METHOD           = 'setJwtOptions';

    // todo: make this prop set via config for tests to run normally
    public const DEFAULT_ACTIVATE = 30;
    public const DEFAULT_EXPIRES  = 3600;

    public const DEFAULT_REDIS_HOST = '127.0.0.1';
    public const DEFAULT_REDIS_PORT = 6379;
}
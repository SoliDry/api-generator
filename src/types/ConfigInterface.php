<?php
namespace rjapi\types;

interface ConfigInterface
{
    const QUERY_PARAMS     = 'query_params';
    const TREES            = 'trees';
    const JWT              = 'jwt';
    const ENABLED          = 'enabled';
    const ACTIVATE         = 'activate';
    const EXPIRES          = 'expires';
    const STATE_MACHINE    = 'state_machine';
    const SPELL_CHECK      = 'spell_check';
    const SPELL_LANGUAGE   = 'spell_language';
    const LANGUAGE         = 'language';
    const DEFAULT_LANGUAGE = 'en';
    const STATES           = 'states';
    const INITIAL          = 'initial';
    const CUSTOM_SQL       = 'custom_sql';
    const QUERY            = 'query';
    const BINDINGS         = 'bindings';

    const DEFAULT_ACTIVATE = 30;
    const DEFAULT_EXPIRES  = 3600;
}
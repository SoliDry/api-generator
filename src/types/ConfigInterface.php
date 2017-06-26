<?php
namespace rjapi\types;

interface ConfigInterface
{
    const QUERY_PARAMS     = 'query_params';
    const TREES            = 'trees';
    const JWT              = 'jwt';
    const ENABLED          = 'enabled';
    const HIDE_MASK        = 'hide_mask';
    const FLAGS            = 'flags';
    const ACTIVATE         = 'activate';
    const EXPIRES          = 'expires';
    const STATE_MACHINE    = 'state_machine';
    const SPELL_CHECK      = 'spell_check';
    const BIT_MASK         = 'bit_mask';
    const SPELL_LANGUAGE   = 'spell_language';
    const LANGUAGE         = 'language';
    const DEFAULT_LANGUAGE = 'en';
    const STATES           = 'states';
    const INITIAL          = 'initial';
    const CUSTOM_SQL       = 'custom_sql';
    const QUERY            = 'query';
    const BINDINGS         = 'bindings';

    const STATE_MACHINE_METHOD = 'setFsmOptions';
    const SPELL_CHECK_METHOD   = 'setSpellOptions';
    const BIT_MASK_METHOD      = 'setBitMaskOptions';

    const DEFAULT_ACTIVATE = 30;
    const DEFAULT_EXPIRES  = 3600;
}
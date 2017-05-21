<?php
namespace rjapi\types;

interface ConfigInterface
{
    const QUERY_PARAMS  = 'query_params';
    const TREES         = 'trees';
    const JWT           = 'jwt';
    const ENABLED       = 'enabled';
    const ACTIVATE      = 'activate';
    const EXPIRES       = 'expires';
    const STATE_MACHINE = 'state_machine';
    const STATES        = 'states';
    const INITIAL       = 'initial';

    const DEFAULT_ACTIVATE = 30;
    const DEFAULT_EXPIRES  = 3600;
}
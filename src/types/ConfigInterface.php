<?php
namespace rjapi\types;

interface ConfigInterface
{
    const QUERY_PARAMS = 'query_params';
    const TREES        = 'trees';
    const JWT          = 'jwt';
    const ENABLED      = 'enabled';
    const ACTIVATE     = 'activate';
    const EXPIRES      = 'expires';

    const DEFAULT_ACTIVATE = 30;
    const DEFAULT_EXPIRES  = 3600;
}
<?php
namespace rjapi\blocks;

interface DefaultInterface
{
    const CONTENT_TYPE = 'application/vnd.api+json';

    const CONTROLLER_POSTFIX = 'Controller';
    const CONTAINER_POSTFIX = 'Container';
    const QUERY_SEARCH_POSTFIX = 'QuerySearch';

    const MIDDLEWARE_POSTFIX = 'Middleware';
    const FORM_BASE = 'Base';
    const FORM_PREFIX = 'Form';
    const FORM_ACTION = 'Action';

    const TABLE_PROPERTY = 'table';
    const PRIMARY_KEY_PROPERTY = 'primaryKey';
    const TIMESTAMPS_PROPERTY = 'timestamps';

    const ANSI_COLOR_RED    =  "\x1b[31m";
    const ANSI_COLOR_GREEN  =  "\x1b[32m";
    const ANSI_COLOR_YELLOW = "\x1b[33m";
    const ANSI_COLOR_RESET  = "\x1b[0m";
}
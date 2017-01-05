<?php
namespace rjapi\blocks;

interface DefaultInterface
{
    const CONTROLLER_POSTFIX = 'Controller';
    const CONTAINER_POSTFIX = 'Container';
    const QUERY_SEARCH_POSTFIX = 'QuerySearch';
    const MIDDLEWARE_POSTFIX = 'Middleware';

    const PREFIX_KEY = 'prefix';

    // console colors
    const ANSI_COLOR_RED    =  "\x1b[31m";
    const ANSI_COLOR_GREEN  =  "\x1b[32m";
    const ANSI_COLOR_YELLOW = "\x1b[33m";
    const ANSI_COLOR_RESET  = "\x1b[0m";
}
<?php
namespace rjapi\types;

interface DefaultInterface
{
    const RAML_EXT           = '.raml';
    const CONTROLLER_POSTFIX = 'Controller';
    const MIDDLEWARE_POSTFIX = 'Middleware';

    const PREFIX_KEY = 'prefix';

    // console colors
    const ANSI_COLOR_RED    =  "\x1b[31m";
    const ANSI_COLOR_GREEN  =  "\x1b[32m";
    const ANSI_COLOR_YELLOW = "\x1b[33m";
    const ANSI_COLOR_RESET  = "\x1b[0m";

    // generated code limiters
    const CLASS_START  = '>>>class>>>';
    const CLASS_END    = '<<<class<<<';
    const PROPS_START  = '>>>props>>>';
    const PROPS_END    = '<<<props<<<';
    const METHOD_START = '>>>methods>>>';
    const METHOD_END   = '<<<methods<<<';
    const ROUTES_START = '>>>routes>>>';
    const ROUTES_END   = '<<<routes<<<';
}
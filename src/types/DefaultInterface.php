<?php
namespace rjapi\types;

interface DefaultInterface
{
    public const YAML_EXT             = '.yaml';
    public const CONTROLLER_POSTFIX   = 'Controller';
    public const FORM_REQUEST_POSTFIX = 'FormRequest';
    // set the functional postfix for Codeception
    public const FUNCTIONAL_POSTFIX = 'Cest';

    public const PREFIX_KEY = 'prefix';

    // console colors
    public const ANSI_COLOR_RED    =  "\x1b[31m";
    public const ANSI_COLOR_GREEN  =  "\x1b[32m";
    public const ANSI_COLOR_YELLOW = "\x1b[33m";
    public const ANSI_COLOR_RESET  = "\x1b[0m";

    // generated code limiters
    public const CLASS_START  = '>>>class>>>';
    public const CLASS_END    = '<<<class<<<';
    public const PROPS_START  = '>>>props>>>';
    public const PROPS_END    = '<<<props<<<';
    public const METHOD_START = '>>>methods>>>';
    public const METHOD_END   = '<<<methods<<<';
    public const ROUTES_START = '>>>routes>>>';
    public const ROUTES_END   = '<<<routes<<<';
}
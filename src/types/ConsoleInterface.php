<?php

namespace rjapi\types;

interface ConsoleInterface
{
    // console command options
    const OPTION_MIGRATIONS    = 'migrations';
    const OPTION_REGENERATE    = 'regenerate';
    const OPTION_APPEND        = 'append';
    const APPEND_DEFAULT_VALUE = 'last';
}
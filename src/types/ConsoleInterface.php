<?php

namespace rjapi\types;

interface ConsoleInterface
{
    // console command options
    const OPTION_MIGRATIONS   = 'migrations';
    const OPTION_REGENERATE   = 'regenerate';
    const OPTION_MERGE        = 'merge';
    const MERGE_DEFAULT_VALUE = 'last';
}
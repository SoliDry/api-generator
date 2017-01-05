<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 05.01.17
 * Time: 13:31
 */

namespace rjapi\blocks;


interface ConsoleInterface
{
    // console command options
    const OPTION_MIGRATIONS = 'migrations';
    const OPTION_REGENERATE = 'regenerate';
}
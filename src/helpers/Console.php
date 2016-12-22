<?php

namespace rjapi\helpers;

class Console
{
    // actions
    const CREATED = 'created';

    // user colors
    const COLOR_RED    = 'red';
    const COLOR_GREEN  = 'green';
    const COLOR_YELLOW = 'yellow';

    const ANSI_COLOR_RED    = "\x1b[31m";
    const ANSI_COLOR_GREEN  = "\x1b[32m";
    const ANSI_COLOR_YELLOW = "\x1b[33m";
    const ANSI_COLOR_RESET  = "\x1b[0m";

    private static $colorMaps = [
        self::COLOR_RED    => self::ANSI_COLOR_RED,
        self::COLOR_GREEN  => self::ANSI_COLOR_GREEN,
        self::COLOR_YELLOW => self::ANSI_COLOR_YELLOW,
    ];

    public static function out(string $str, $color = null)
    {
        echo (($color === null) ? '' : self::$colorMaps[$color]) . $str . (($color === null) ? '' : self::ANSI_COLOR_RESET) . PHP_EOL;
    }
}
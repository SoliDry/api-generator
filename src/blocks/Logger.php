<?php

namespace rjapi\blocks;

class Logger
{
    public static function log($str, $file = 'main.log')
    {
        $path = __DIR__ . '/../../logs/' . $file;
        $time = date('Y-m-d H:i:s');
        $ip   = (empty($_SERVER['REMOTE_ADDR'])) ? 'NO IP' : $_SERVER['REMOTE_ADDR'];
        $str  = '[' . $file . '][' . $time . '] - IP[' . $ip . '] ' . $str . PHP_EOL;
        file_put_contents($path, $str, FILE_APPEND);
    }
}
<?php

namespace rjapi\helpers;

use rjapi\types\PhpInterface;

class MigrationsHelper
{
    const PATTERN_SPLIT_UC = '/(?=[A-Z])/';

    public static function getTableName(string $objectName)
    {
        $table = '';
        // make entity lc + underscore
        $words = preg_split(self::PATTERN_SPLIT_UC, lcfirst($objectName));
        foreach($words as $key => $word)
        {
            $table .= $word;
            if(empty($words[$key + 1]) === false)
            {
                $table .= PhpInterface::UNDERSCORE;
            }
        }

        return strtolower($table);
    }
}
<?php

namespace SoliDry\Helpers;

use SoliDry\Types\PhpInterface;

/**
 * Class MigrationsHelper
 * @package SoliDry\Helpers
 */
class MigrationsHelper
{
    private const PATTERN_SPLIT_UC = '/(?=[A-Z])/';
    private const PATTERN_MERGE_UC = '/(?=[_][A-Z-a-z].)/';
    private const DOUBLE_UNDERSCORE = '__';

    /**
     * Generates table_name from TableName objects
     * @param string $objectName
     * @return string
     */
    public static function getTableName(string $objectName): string
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

        // need post-processing of dbl underscore due to there can be intended underscores in naming by user
        $table = str_replace(self::DOUBLE_UNDERSCORE, PhpInterface::UNDERSCORE, $table);
        return strtolower($table);
    }

    /**
     * Generates table_name from TableName objects
     * @param string $tableName
     * @return string
     */
    public static function getObjectName(string $tableName): string
    {
        $table = '';
        // make entity lc + underscore
        $words = preg_split(self::PATTERN_MERGE_UC, $tableName);
        foreach($words as $key => $word)
        {
            $table .= ucfirst(str_replace(PhpInterface::UNDERSCORE, '', $word));
        }

        return $table;
    }
}

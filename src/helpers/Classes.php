<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 10.12.16
 * Time: 9:57
 */

namespace rjapi\helpers;


class Classes
{
    /**
     * @param string $class
     * @return string
     */
    public static function getName(string $class)
    {
        $ref = new \ReflectionClass($class);
        return (string) $ref->getShortName();
    }
}
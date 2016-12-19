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
     *
     * @return string
     */
    public static function getName(string $class): string
    {
        $ref = new \ReflectionClass($class);

        return (string) $ref->getShortName();
    }

    public static function getObjectName($object): string
    {
        $ref = new \ReflectionClass($object);

        return (string) $ref->getShortName();
    }

    public static function cutEntity(string $str, string $postfix)
    {
        return substr($str, 0, strpos($str, $postfix));
    }
}
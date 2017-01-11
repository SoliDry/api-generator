<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 10.12.16
 * Time: 9:57
 */

namespace rjapi\helpers;

use rjapi\blocks\DirsInterface;
use rjapi\blocks\PhpEntitiesInterface;

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

    /**
     * @param $object
     *
     * @return string
     */
    public static function getObjectName($object): string
    {
        $ref = new \ReflectionClass($object);

        return (string) $ref->getShortName();
    }

    /**
     * @param string $str
     * @param string $postfix
     *
     * @return mixed
     */
    public static function cutEntity(string $str, string $postfix)
    {
        return substr($str, 0, strpos($str, $postfix));
    }

    /**
     * @param string $entity
     * @return string
     */
    public static function getModelEntity(string $entity): string
    {
        return DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . Config::getModuleName() .
        PhpEntitiesInterface::BACKSLASH . DirsInterface::ENTITIES_DIR .
        PhpEntitiesInterface::BACKSLASH . $entity;
    }

    /**
     * Gets class name ucwording 1st and replacing -_ in composite names
     * @param string $objectName
     *
     * @return string
     */
    public static function getClassName(string $objectName): string
    {
        return str_replace(
            [
                PhpEntitiesInterface::DASH,
                PhpEntitiesInterface::UNDERSCORE
            ], '', ucwords(
                $objectName, PhpEntitiesInterface::DASH . PhpEntitiesInterface::UNDERSCORE
            )
        );
    }
}
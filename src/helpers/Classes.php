<?php
namespace rjapi\helpers;

use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;

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
        return DirsInterface::MODULES_DIR . PhpInterface::BACKSLASH . ConfigHelper::getModuleName() .
        PhpInterface::BACKSLASH . DirsInterface::ENTITIES_DIR .
        PhpInterface::BACKSLASH . $entity;
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
                PhpInterface::DASH,
                PhpInterface::UNDERSCORE
            ], '', ucwords(
                $objectName, PhpInterface::DASH . PhpInterface::UNDERSCORE
            )
        );
    }
}
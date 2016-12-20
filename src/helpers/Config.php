<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20.12.16
 * Time: 22:22
 */

namespace rjapi\helpers;


use rjapi\blocks\ModulesInterface;
use rjapi\blocks\PhpEntitiesInterface;

class Config
{
    public static function getConfigKey(): string
    {
        $conf = config();
        $arr = $conf[ModulesInterface::KEY_MODULE][ModulesInterface::KEY_MODULES];
        return end($arr);
    }
    
    public static function getModuleName(): string
    {
        return config(self::getConfigKey() . PhpEntitiesInterface::DOT . ModulesInterface::KEY_NAME);
    }
}
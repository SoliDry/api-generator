<?php
namespace rjapi\helpers;


use rjapi\blocks\ConfigInterface;
use rjapi\blocks\ModelsInterface;
use rjapi\blocks\ModulesInterface;
use rjapi\blocks\PhpEntitiesInterface;

class Config
{
    private static $availableQueryParams = [
        ModelsInterface::PARAM_PAGE  => ModelsInterface::DEFAULT_PAGE,
        ModelsInterface::PARAM_LIMIT => ModelsInterface::DEFAULT_LIMIT,
        ModelsInterface::PARAM_SORT  => ModelsInterface::DEFAULT_SORT,
    ];

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

    public static function getQueryParam(string $param)
    {
        if (array_key_exists($param, self::$availableQueryParams))
        {
            $params = config(self::getConfigKey() . PhpEntitiesInterface::DOT . ConfigInterface::QUERY_PARAMS);
            return (empty($params[$param])) ? self::$availableQueryParams[$param] : $params[$param];
        }
        return null;
    }
}
<?php

namespace SoliDry\Helpers;


use SoliDry\Types\ConfigInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\ModulesInterface;
use SoliDry\Types\PhpInterface;

/**
 * Class ConfigHelper
 * helps get/set entities in application module config
 *
 * @package SoliDry\Helpers
 */
class ConfigHelper
{
    private static array $availableQueryParams = [
        ModelsInterface::PARAM_PAGE => ModelsInterface::DEFAULT_PAGE,
        ModelsInterface::PARAM_LIMIT => ModelsInterface::DEFAULT_LIMIT,
        ModelsInterface::PARAM_SORT => ModelsInterface::DEFAULT_SORT,
    ];

    /**
     * @return string
     */
    public static function getConfigKey(): string
    {
        $conf = config();
        $arr = $conf[ModulesInterface::KEY_MODULE][ModulesInterface::KEY_MODULES];

        return end($arr);
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        return config(self::getConfigKey() . PhpInterface::DOT . ModulesInterface::KEY_NAME);
    }

    /**
     * @param string $param
     * @return mixed|null
     */
    public static function getQueryParam(string $param)
    {
        if (array_key_exists($param, self::$availableQueryParams)) {
            $params = config(self::getConfigKey() . PhpInterface::DOT . ConfigInterface::QUERY_PARAMS);

            return empty($params[$param]) ? self::$availableQueryParams[$param] : $params[$param];
        }

        return null;
    }

    /**
     * @param string $entity
     * @param string $param
     * @param bool $lower
     * @return mixed|null
     */
    public static function getNestedParam(string $entity, string $param, bool $lower = false)
    {
        if ($lower === true) {
            $param = strtolower($param);
        }
        $params = self::getParam($entity);

        return empty($params[$param]) ? null : $params[$param];
    }

    /**
     * @param string $entity
     * @return \Illuminate\Config\Repository|mixed
     */
    public static function getParam(string $entity)
    {
        return config(self::getConfigKey() . PhpInterface::DOT . $entity);
    }
}

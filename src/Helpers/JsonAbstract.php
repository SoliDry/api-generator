<?php

namespace SoliDry\Helpers;


use SoliDry\Types\ApiInterface;

abstract class JsonAbstract
{
    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getAttributes(array $jsonApiArr) : array
    {
        return empty($jsonApiArr[ApiInterface::RAML_DATA][ApiInterface::RAML_ATTRS]) ? [] : $jsonApiArr[ApiInterface::RAML_DATA][ApiInterface::RAML_ATTRS];
    }

    /**
     * Returns an array of bulk attributes for each element
     *
     * @param array $jsonApiArr
     * @return array
     */
    public static function getBulkAttributes(array $jsonApiArr) : array
    {
        return empty($jsonApiArr[ApiInterface::RAML_DATA]) ? [] : $jsonApiArr[ApiInterface::RAML_DATA];
    }

    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getRelationships(array $jsonApiArr) : array
    {
        return empty($jsonApiArr[ApiInterface::RAML_DATA][ApiInterface::RAML_RELATIONSHIPS]) ? [] : $jsonApiArr[ApiInterface::RAML_DATA][ApiInterface::RAML_RELATIONSHIPS];
    }

    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getData(array $jsonApiArr) : array
    {
        return empty($jsonApiArr[ApiInterface::RAML_DATA]) ? [] : $jsonApiArr[ApiInterface::RAML_DATA];
    }

    /**
     * Encoder array -> json
     *
     * @param array $array
     * @param int $opts
     * @return string
     */
    public static function encode(array $array, int $opts = 0): string
    {
        return json_encode($array, $opts);
    }

    /**
     * Decoder json -> array
     *
     * @param mixed $json
     * @return mixed
     */
    public static function decode(string $json): array
    {
        return json_decode($json, true);
    }

    /**
     * This method is wrapper over decode to let polymorphism between raml/json parsers
     *
     * @param string $json
     * @return array
     */
    public static function parse(string $json): array
    {
        return self::decode($json);
    }
}
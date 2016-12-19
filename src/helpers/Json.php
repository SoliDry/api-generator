<?php

namespace rjapi\helpers;

use rjapi\blocks\RamlInterface;

class Json
{
    /**
     * @param string $json
     *
     * @return array
     */
    public static function parse(string $json): array
    {
        return json_decode($json, true);
    }

    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getAttributes(array $jsonApiArr): array
    {
        return $jsonApiArr[RamlInterface::RAML_DATA][RamlInterface::RAML_ATTRS];
    }
}
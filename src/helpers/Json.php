<?php

namespace rjapi\helpers;

use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use rjapi\blocks\RamlInterface;

class Json
{
    const CONTENT_TYPE = 'application/vnd.api+json';

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

    public static function outputSerializedData(ResourceInterface $resource): string
    {
        $host = $_SERVER['HTTP_HOST'];
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer($host));
        header('Content-Type: ' . self::CONTENT_TYPE);
        echo $manager->createData($resource)->toJson();
    }
}
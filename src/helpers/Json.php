<?php

namespace rjapi\helpers;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use rjapi\blocks\RamlInterface;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;
use rjapi\extension\JSONApiInterface;
use rjapi\transformers\DefaultTransformer;

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

    public static function getResource(BaseFormRequest $middleware, BaseModel $model, string $entity, $isCollection = false)
    {
        $transformer = new DefaultTransformer($middleware);
        if($isCollection === true)
        {
            return new Collection($model, $transformer, strtolower($entity));
        }
        return new Item($model, $transformer, strtolower($entity));
    }

    public static function outputSerializedData(ResourceInterface $resource, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK)
    {
        http_response_code($responseCode);
        header('Content-Type: ' . self::CONTENT_TYPE);
        if ($responseCode === JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT) {
            exit;
        }
        $host = $_SERVER['HTTP_HOST'];
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer($host));
        echo $manager->createData($resource)->toJson();
    }
}
<?php

namespace rjapi\helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use rjapi\blocks\RamlInterface;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\JSONApiInterface;
use rjapi\transformers\DefaultTransformer;

class Json
{
    /**
     * @param string $json
     *
     * @return array
     */
    public static function parse($json): array
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
        return empty($jsonApiArr[RamlInterface::RAML_DATA][RamlInterface::RAML_ATTRS]) ? [] : $jsonApiArr[RamlInterface::RAML_DATA][RamlInterface::RAML_ATTRS];
    }

    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getRelationships(array $jsonApiArr): array
    {
        return empty($jsonApiArr[RamlInterface::RAML_DATA][RamlInterface::RAML_RELATIONSHIPS]) ? [] : $jsonApiArr[RamlInterface::RAML_DATA][RamlInterface::RAML_RELATIONSHIPS];
    }

    /**
     * @param array $jsonApiArr
     *
     * @return array
     */
    public static function getData(array $jsonApiArr): array
    {
        return empty($jsonApiArr[RamlInterface::RAML_DATA]) ? [] : $jsonApiArr[RamlInterface::RAML_DATA];
    }

    /**
     * @param $relation      \Illuminate\Database\Eloquent\Collection | Model
     * @param string $entity
     * @return array JSON API rels compatible array
     */
    public static function getRelations($relation, string $entity)
    {
        $jsonArr = [];
        if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
            $cnt = count($relation);
            if ($cnt > 1) {
                foreach ($relation as $v) {
                    $attrs = $v->getAttributes();
                    $jsonArr[] = [RamlInterface::RAML_TYPE => $entity,
                                  RamlInterface::RAML_ID   => $attrs[RamlInterface::RAML_ID]];
                }
            } else {
                foreach ($relation as $v) {
                    $attrs = $v->getAttributes();
                    $jsonArr = [RamlInterface::RAML_TYPE => $entity,
                                RamlInterface::RAML_ID   => $attrs[RamlInterface::RAML_ID]];
                }
            }
        }
        return $jsonArr;
    }

    /**
     * Output errors in JSON API compatible format
     * @param array $errors
     */
    public static function outputErrors(array $errors)
    {
        $arr[JSONApiInterface::CONTENT_ERRORS] = [];
        if (empty($errors) === false)
        {
            $arr[JSONApiInterface::CONTENT_ERRORS] = $errors;
        }
        echo self::encode($arr);
        exit(JSONApiInterface::EXIT_STATUS_ERROR);
    }

    /**
     * @param Request $request
     * @param array $data
     */
    public static function outputSerializedRelations(Request $request, array $data)
    {
        http_response_code(JSONApiInterface::HTTP_RESPONSE_CODE_OK);
        header(JSONApiInterface::HEADER_CONTENT_TYPE . JSONApiInterface::HEADER_CONTENT_TYPE_VALUE);

        $arr[JSONApiInterface::CONTENT_LINKS] = [
            JSONApiInterface::CONTENT_SELF => $request->getUri(),
        ];
        $arr[JSONApiInterface::CONTENT_DATA] = $data;
        echo self::encode($arr);
        exit(JSONApiInterface::EXIT_STATUS_SUCCESS);
    }

    /**
     * @param BaseFormRequest $middleware
     * @param                 $model
     * @param string $entity
     * @param bool $isCollection
     *
     * @return Collection|Item
     */
    public static function getResource(BaseFormRequest $middleware, $model, string $entity, $isCollection = false)
    {
        $transformer = new DefaultTransformer($middleware);
        if ($isCollection === true) {
            return new Collection($model, $transformer, strtolower($entity));
        }

        return new Item($model, $transformer, strtolower($entity));
    }

    /**
     * @param ResourceInterface $resource
     * @param int $responseCode
     */
    public static function outputSerializedData(ResourceInterface $resource, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK)
    {
        http_response_code($responseCode);
        header(JSONApiInterface::HEADER_CONTENT_TYPE . JSONApiInterface::HEADER_CONTENT_TYPE_VALUE);
        if ($responseCode === JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT) {
            exit;
        }
        $host = $_SERVER['HTTP_HOST'];
        $manager = new Manager();

        if (isset($_GET['include'])) {
            $manager->parseIncludes($_GET['include']);
        }
        $manager->setSerializer(new JsonApiSerializer($host));
        echo $manager->createData($resource)->toJson();
        exit(JSONApiInterface::EXIT_STATUS_SUCCESS);
    }

    /**
     * @param array $array
     * @return string
     */
    public static function encode(array $array)
    {
        return json_encode($array);
    }
}
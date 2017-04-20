<?php

namespace rjapi\helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\JSONApiInterface;
use rjapi\transformers\DefaultTransformer;

class Json
{
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
        if($relation instanceof \Illuminate\Database\Eloquent\Collection) {
            $cnt = count($relation);
            if($cnt > 1) {
                foreach($relation as $v) {
                    $attrs = $v->getAttributes();
                    $jsonArr[] = [RamlInterface::RAML_TYPE => $entity,
                                  RamlInterface::RAML_ID   => $attrs[RamlInterface::RAML_ID]];
                }
            }
            else {
                foreach($relation as $v) {
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
        if(empty($errors) === false) {
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
     * @param array $meta
     * @return Collection|Item
     */
    public static function getResource(BaseFormRequest $middleware, $model, string $entity, bool $isCollection = false, array $meta = [])
    {
        $transformer = new DefaultTransformer($middleware);
        if($isCollection === true) {
            $collection = new Collection($model, $transformer, strtolower($entity));
            if(empty($meta) === false) {
                $collection->setMeta($meta);
            }

            return $collection;
        }
        $item = new Item($model, $transformer, strtolower($entity));
        $item->setMeta($meta);
        return $item;
    }

    /**
     * @param ResourceInterface $resource
     * @param int $responseCode
     * @param array $data
     */
    public static function outputSerializedData(ResourceInterface $resource, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK,
                                                $data = ModelsInterface::DEFAULT_DATA)
    {
        http_response_code($responseCode);
        header(JSONApiInterface::HEADER_CONTENT_TYPE . JSONApiInterface::HEADER_CONTENT_TYPE_VALUE);
        if($responseCode === JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT) {
            exit;
        }
        $host = $_SERVER['HTTP_HOST'];
        $manager = new Manager();

        if(isset($_GET['include'])) {
            $manager->parseIncludes($_GET['include']);
        }
        $manager->setSerializer(new JsonApiSerializer($host));
        echo self::getSelectedData($manager->createData($resource)->toJson(), $data);
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

    /**
     * @param mixed $json
     * @return mixed
     */
    public static function decode($json)
    {
        return json_decode($json, true);
    }

    /**
     * @param string $json
     * @param array $data
     * @return string
     */
    private static function getSelectedData(string $json, array $data): string
    {
        if(current($data) === PhpInterface::ASTERISK) {// do nothing - grab all fields
            return $json;
        }
        $jsonArr = self::decode($json);
        $current = current($jsonArr[RamlInterface::RAML_DATA]);
        if(empty($current[JSONApiInterface::CONTENT_ATTRIBUTES]) === false) {// this is an array of values
            self::unsetArray($jsonArr, $data);
        }
        else {// this is just one element
            self::unsetObject($jsonArr, $data);
        }

        return self::encode($jsonArr);
    }

    /**
     *
     * @param array &$json
     * @param array $data
     */
    private static function unsetArray(array &$json, array $data)
    {
        foreach($json as &$jsonObject) {
            foreach($jsonObject as &$v) {
                foreach($v[JSONApiInterface::CONTENT_ATTRIBUTES] as $key => $attr) {
                    if(in_array($key, $data) === false) {
                        unset($v[JSONApiInterface::CONTENT_ATTRIBUTES][$key]);
                    }
                }
            }
        }
    }

    /**
     * @param array $json
     * @param array $data
     */
    private static function unsetObject(array &$json, array $data)
    {
        foreach($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES] as $k => $v) {
            if(in_array($k, $data) === false) {
                unset($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES][$k]);
            }
        }
    }
}
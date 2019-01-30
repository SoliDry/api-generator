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
use rjapi\types\ApiInterface;
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
     * @param $relations      \Illuminate\Database\Eloquent\Collection
     * @param string $entity
     * @return array JSON API rels compatible array
     */
    public static function getRelations($relations, string $entity) : array
    {
        $jsonArr = [];
        if ($relations instanceof \Illuminate\Database\Eloquent\Collection) {
            $cnt = count($relations);
            if ($cnt > 1) {
                foreach ($relations as $v) {
                    $attrs     = $v->getAttributes();
                    $jsonArr[] = [ApiInterface::RAML_TYPE => $entity,
                                  ApiInterface::RAML_ID   => $attrs[ApiInterface::RAML_ID]];
                }
            } else {
                foreach ($relations as $v) {
                    $attrs   = $v->getAttributes();
                    $jsonArr = [ApiInterface::RAML_TYPE => $entity,
                                ApiInterface::RAML_ID   => $attrs[ApiInterface::RAML_ID]];
                }
            }
        } elseif ($relations instanceof Model) {
            $attrs   = $relations->getAttributes();
            $jsonArr = [ApiInterface::RAML_TYPE => $entity,
                        ApiInterface::RAML_ID   => $attrs[ApiInterface::RAML_ID]];
        }

        return $jsonArr;
    }

    /**
     * Output errors in JSON API compatible format
     * @param array $errors
     * @param bool $return
     * @return string
     */
    public static function outputErrors(array $errors, bool $return = false)
    {
        $arr[JSONApiInterface::CONTENT_ERRORS] = [];
        if (empty($errors) === false) {
            $arr[JSONApiInterface::CONTENT_ERRORS] = $errors;
        }
        // errors and codes must be clear with readable json
        $encoded = self::encode($arr, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
        if (false === $return && env('APP_ENV') !== 'dev') {
            echo $encoded;
            exit(JSONApiInterface::EXIT_STATUS_ERROR);
        }
        return $encoded;
    }

    /**
     * Returns composition of relations
     *
     * @param Request $request
     * @param array $data
     * @return string
     */
    public static function prepareSerializedRelations(Request $request, array $data) : string
    {
        $arr[JSONApiInterface::CONTENT_LINKS] = [
            JSONApiInterface::CONTENT_SELF => $request->getUri(),
        ];

        $arr[JSONApiInterface::CONTENT_DATA] = $data;

        return self::encode($arr);
    }

    /**
     * @param BaseFormRequest $formRequest
     * @param                 $model
     * @param string $entity
     * @param bool $isCollection
     *
     * @param array $meta
     * @return Collection|Item
     */
    public static function getResource(BaseFormRequest $formRequest, $model, string $entity, bool $isCollection = false, array $meta = [])
    {
        $transformer = new DefaultTransformer($formRequest);
        if ($isCollection === true) {
            $collection = new Collection($model, $transformer, strtolower($entity));
            if (empty($meta) === false) {
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
     * @param array $data
     * @return string
     */
    public static function prepareSerializedData(ResourceInterface $resource, $data = ModelsInterface::DEFAULT_DATA) : string
    {
        if (empty($resource->getData())) { // preventing 3d party libs (League etc) from crash on empty data
            return self::encode([
                ModelsInterface::PARAM_DATA => []
            ]);
        }

        $host    = $_SERVER['HTTP_HOST'];
        $manager = new Manager();

        if (isset($_GET['include'])) {
            $manager->parseIncludes($_GET['include']);
        }

        $manager->setSerializer(new JsonApiSerializer($host));
        return self::getSelectedData($manager->createData($resource)->toJson(), $data);
    }

    /**
     * @param array $array
     * @param int $opts
     * @return string
     */
    public static function encode(array $array, int $opts = 0)
    {
        return json_encode($array, $opts);
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
    private static function getSelectedData(string $json, array $data) : string
    {
        if (current($data) === PhpInterface::ASTERISK) {// do nothing - grab all fields
            return $json;
        }

        $jsonArr = self::decode($json);
        $current = current($jsonArr[ApiInterface::RAML_DATA]);

        if (empty($current[JSONApiInterface::CONTENT_ATTRIBUTES]) === false) {// this is an array of values
            self::unsetArray($jsonArr, $data);
        } else {// this is just one element
            self::unsetObject($jsonArr, $data);
        }

        return self::encode($jsonArr);
    }

    /**
     *
     * @param array &$json
     * @param array $data
     */
    private static function unsetArray(array &$json, array $data) : void
    {
        foreach ($json as &$jsonObject) {
            foreach ($jsonObject as &$v) {
                foreach ($v[JSONApiInterface::CONTENT_ATTRIBUTES] as $key => $attr) {
                    if (in_array($key, $data) === false) {
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
    private static function unsetObject(array &$json, array $data) : void
    {
        foreach ($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES] as $k => $v) {
            if (in_array($k, $data) === false) {
                unset($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES][$k]);
            }
        }
    }
}
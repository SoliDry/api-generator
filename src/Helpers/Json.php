<?php

namespace SoliDry\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Transformers\DefaultTransformer;
use SoliDry\Helpers\Request as Req;

/**
 * Class Json
 * @package SoliDry\Helpers
 */
class Json extends JsonAbstract
{
    private $isCollection = false;
    private $meta = [];

    /**
     * @param $relations      \Illuminate\Database\Eloquent\Collection
     * @param string $entity
     * @return array JSON API rels compatible array
     */
    public static function getRelations($relations, string $entity) : array
    {
        $jsonArr = [];
        if ($relations instanceof \Illuminate\Database\Eloquent\Collection) {
            $cnt = \count($relations);
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
        if ($return === false && env('APP_ENV') !== 'dev') {
            echo $encoded;
            exit(JSONApiInterface::EXIT_STATUS_ERROR);
        }
        return $encoded;
    }

    /**
     *
     * @param array $errors
     * @return string
     */
    public function getErrors(array $errors) : string
    {
        $arr[JSONApiInterface::CONTENT_ERRORS] = [];
        if (empty($errors) === false) {
            $arr[JSONApiInterface::CONTENT_ERRORS] = $errors;
        }

        return self::encode($arr, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
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
     *
     * @return Collection|Item
     */
    public function getResource(BaseFormRequest $formRequest, $model, string $entity)
    {
        $transformer = new DefaultTransformer($formRequest);
        if ($this->isCollection === true) {
            $collection = new Collection($model, $transformer, MigrationsHelper::getTableName($entity));
            if (empty($this->meta) === false) {
                $collection->setMeta($this->meta);
            }

            if ($model instanceof LengthAwarePaginator) { // only for paginator
                $collection->setPaginator(new IlluminatePaginatorAdapter($model));
            }

            return $collection;
        }

        $item = new Item($model, $transformer, MigrationsHelper::getTableName($entity));
        $item->setMeta($this->meta);

        return $item;
    }

    /**
     * Prepares data to output in json-api format
     *
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

        $manager = new Manager();
        if (isset($_GET['include'])) {
            $manager->parseIncludes($_GET['include']);
        }

        $manager->setSerializer(new JsonApiSerializer((new Req())->getBasePath()));
        return self::getSelectedData($manager->createData($resource)->toJson(), $data);
    }

    /**
     * Gets data only with those elements of object/array that should be provided as output
     *
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
     * Unsets objects from array that shouldn't be provided as output
     *
     * @param array &$json
     * @param array $data
     */
    private static function unsetArray(array &$json, array $data) : void
    {
        foreach ($json as $type => &$jsonObject) {

            if ($type === ApiInterface::RAML_DATA) { // unset only data->attributes fields
                foreach ($jsonObject as &$v) {

                    if (empty($v[JSONApiInterface::CONTENT_ATTRIBUTES]) === false) { // can be any of meta/link
                        foreach ($v[JSONApiInterface::CONTENT_ATTRIBUTES] as $key => $attr) {

                            if (\in_array($key, $data, true) === false) {
                                unset($v[JSONApiInterface::CONTENT_ATTRIBUTES][$key]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Unsets objects that shouldn't be provided as output
     *
     * @param array $json
     * @param array $data
     */
    private static function unsetObject(array &$json, array $data) : void
    {
        if (empty($json[JSONApiInterface::CONTENT_DATA]) === false
            && empty($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES]) === false) {

            foreach ($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES] as $k => $v) {
                if (\in_array($k, $data, true) === false) {
                    unset($json[JSONApiInterface::CONTENT_DATA][JSONApiInterface::CONTENT_ATTRIBUTES][$k]);
                }
            }
        }
    }

    /**
     * @param bool $isCollection
     * @return Json
     */
    public function setIsCollection(bool $isCollection): Json
    {
        $this->isCollection = $isCollection;

        return $this;
    }

    /**
     * @param array $meta
     * @return Json
     */
    public function setMeta(array $meta): Json
    {
        $this->meta = $meta;

        return $this;
    }
}
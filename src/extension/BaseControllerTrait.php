<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 18.12.16
 * Time: 19:11
 */

namespace rjapi\extension;

use Illuminate\Http\Request;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\ModelsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Json;

trait BaseControllerTrait
{
    private $props = [];
    private $entity = null;
    private $model = null;
    private $modelEntity = null;
    private $middleWare = null;

    private $methods = [
        self::URI_METHOD_INDEX => self::HTTP_METHOD_GET,
        self::URI_METHOD_VIEW => self::HTTP_METHOD_GET,
        self::URI_METHOD_CREATE => self::HTTP_METHOD_POST,
        self::URI_METHOD_UPDATE => self::HTTP_METHOD_PATCH,
        self::URI_METHOD_DELETE => self::HTTP_METHOD_DELETE,
    ];

    public function __construct()
    {
        print_r(config());die;
        $this->entity = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $middlewareEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . config('v2.name') .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->entity .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $this->middleWare = new $middlewareEntity();
        $this->props = get_object_vars($this->middleWare);

        $this->modelEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . config('v2.name') .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::ENTITIES_DIR . PhpEntitiesInterface::BACKSLASH . $this->entity;
        $this->model = new $this->modelEntity();
    }

    /**
     * Output all entries for this Entity
     */
    public function index()
    {
        $items = $this->getAllEntities();
        $resource = Json::getResource($this->middleWare, $items, $this->entity, true);
        Json::outputSerializedData($resource);
    }

    /**
     * Output one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function view(int $id)
    {
        $item = $this->getEntity($id);
        $resource = Json::getResource($this->middleWare, $item, $this->entity);
        Json::outputSerializedData($resource);
    }

    /**
     * Creates one entry specified by all input fields in $request
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $jsonApiAttributes = Json::getAttributes(Json::parse($request->getContent()));
        foreach ($this->props as $k => $v) {
            // request fields should match Middleware fields
            if (empty($jsonApiAttributes[$k]) === false) {
                $this->model->$k = $jsonApiAttributes[$k];
            }
        }
        $this->model->save();
        $resource = Json::getResource($this->middleWare, $this->model, $this->entity);
        Json::outputSerializedData($resource, HTTPMethodsInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * Updates one entry determined by unique id as uri param for specified fields in $request
     *
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        // get json raw input and parse attrs
        $jsonApiAttributes = Json::getAttributes(Json::parse($request->getContent()));
        $model = $this->getEntity($id);
        foreach ($this->props as $k => $v) {
            // request fields should match Middleware fields
            if (empty($jsonApiAttributes[$k]) === false) {
                $model->$k = $jsonApiAttributes[$k];
            }
        }
        $model->save();
        $resource = Json::getResource($this->middleWare, $model, $this->entity);
        Json::outputSerializedData($resource);
    }

    /**
     * Deletes one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $entity = $this->getEntity($id);
        $entity->destroy();
        Json::outputSerializedData($entity, HTTPMethodsInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }

    private function getEntity(int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, ['id', $id]
        );

        return $obj->first()->get();
    }

    private function getAllEntities(int $count = ModelsInterface::DEFAULT_LIMIT, int $page = 1)
    {
        $from = ($count * $page) - $count;
        $to = $count * $page;
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON . ModelsInterface::MODEL_METHOD_ORDER_BY,
            ['id', ModelsInterface::SQL_DESC]
        );
        return $obj->take($to)->skip($from)->get();
    }

}
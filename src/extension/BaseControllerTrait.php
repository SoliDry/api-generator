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
    private $props  = [];
    private $entity = null;
    private $model  = null;

    private $methods = [
        self::URI_METHOD_INDEX  => self::HTTP_METHOD_GET,
        self::URI_METHOD_VIEW   => self::HTTP_METHOD_GET,
        self::URI_METHOD_CREATE => self::HTTP_METHOD_POST,
        self::URI_METHOD_UPDATE => self::HTTP_METHOD_PATCH,
        self::URI_METHOD_DELETE => self::HTTP_METHOD_DELETE,
    ];

    public function __construct()
    {
        $this->entity     = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $middlewareEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . config('v2.name') .
                            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
                            PhpEntitiesInterface::BACKSLASH .
                            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
                            $this->entity .
                            DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleware       = new $middlewareEntity();
        $this->props      = get_object_vars($middleware);

        $modelEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . config('v2.name') .
                       PhpEntitiesInterface::BACKSLASH . DirsInterface::ENTITIES_DIR . $this->entity;
        $this->model = new $modelEntity();
    }

    /**
     * Output all entries for this Entity
     */
    public function index()
    {
        $items = $this->getAllEntities();
        $rows  = $items->orderBy('id')->take(ModelsInterface::DEFAULT_LIMIT);
    }

    /**
     * Output one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function view(int $id)
    {
        $item = $this->getEntity($id);
        $row  = $item->get();
    }

    /**
     * Creates one entry specified by all input fields in $request
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $jsonApiAttributes = Json::getAttributes(Json::parse($request->getContent()));
        foreach($this->props as $k => $v)
        {
            // request fields should match Middleware fields
            if(empty($jsonApiAttributes[$k]) === false)
            {
                $this->model->$k = $jsonApiAttributes[$k];
            }
        }
        $this->model->save();
    }

    /**
     * Updates one entry determined by unique id as uri param for specified fields in $request
     *
     * @param Request $request
     * @param int     $id
     */
    public function update(Request $request, int $id)
    {
        // get json raw input and parse attrs
        $jsonApiAttributes = Json::getAttributes(Json::parse($request->getContent()));
        // get model ex.: Article::where('id', 123)
        $entity            = $this->getEntity($id);
        foreach($this->props as $k => $v)
        {
            // request fields should match Middleware fields
            if(empty($jsonApiAttributes[$k]) === false)
            {
                $entity->$k = $jsonApiAttributes[$k];
            }
        }
        $entity->save();
    }

    /**
     * Deletes one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function delete(int $id)
    {
    }

    private function getEntity(int $id)
    {
        return call_user_func($this->model . PhpEntitiesInterface::DOUBLE_COLON . 'where(\'id\', ' . $id . ')');
    }

    private function getAllEntities()
    {
        return call_user_func($this->model . PhpEntitiesInterface::DOUBLE_COLON . ModelsInterface::MODEL_METHOD_ALL);
    }
}
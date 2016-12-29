<?php
namespace rjapi\extension;

use Illuminate\Http\Request;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\ModelsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Config;
use rjapi\helpers\Json;

trait BaseControllerTrait
{
    private $props = [];
    private $entity = null;
    private $model = null;
    private $modelEntity = null;
    private $middleWare = null;

    private $methods = [
        self::URI_METHOD_INDEX  => self::HTTP_METHOD_GET,
        self::URI_METHOD_VIEW   => self::HTTP_METHOD_GET,
        self::URI_METHOD_CREATE => self::HTTP_METHOD_POST,
        self::URI_METHOD_UPDATE => self::HTTP_METHOD_PATCH,
        self::URI_METHOD_DELETE => self::HTTP_METHOD_DELETE,
    ];

    public function __construct()
    {
        $this->entity = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $middlewareEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . Config::getModuleName() .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->entity .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $this->middleWare = new $middlewareEntity();
        $this->props = get_object_vars($this->middleWare);

        $this->modelEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . Config::getModuleName() .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::ENTITIES_DIR . PhpEntitiesInterface::BACKSLASH . $this->entity;
        $this->model = new $this->modelEntity();
    }

    /**
     * GET Output all entries for this Entity
     */
    public function index()
    {
        $items = $this->getAllEntities();
        $resource = Json::getResource($this->middleWare, $items, $this->entity, true);
        Json::outputSerializedData($resource);
    }

    /**
     * GET Output one entry determined by unique id as uri param
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
     * POST Creates one entry specified by all input fields in $request
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $json = Json::parse($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);
        foreach ($this->props as $k => $v) {
            // request fields should match Middleware fields
            if (isset($jsonApiAttributes[$k])) {
                $this->model->$k = $jsonApiAttributes[$k];
            }
        }
        $this->model->save();

        $jsonApiRels = Json::getRelationships($json);
        if (empty($jsonApiRels) === false) {
            foreach ($jsonApiRels as $entity => $value) {
                foreach ($value[RamlInterface::RAML_DATA] as $index => $val) {
                    $rId = $val[RamlInterface::RAML_ID];
                    // if pivot file exists then save
                    $ucEntity = ucfirst($entity);
                    $file = DirsInterface::MODULES_DIR . PhpEntitiesInterface::SLASH
                        . Config::getModuleName() . PhpEntitiesInterface::SLASH .
                        DirsInterface::ENTITIES_DIR .
                        $this->entity . PhpEntitiesInterface::PHP_EXT;
                    if (file_exists($file)) { // ManyToMany rel
                        $pivot = new $this->entity . $ucEntity();
                        $pivot->$entity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID = $rId;
                        $pivot->{$this->entity} . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID = $this->model->id;
                    } else { // OneToOne/OneToMany
                        $refModel = new $ucEntity();
                        
                    }
                }
            }
        }

        $resource = Json::getResource($this->middleWare, $this->model, $this->entity);
        Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * PATCH Updates one entry determined by unique id as uri param for specified fields in $request
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
     * DELETE Deletes one entry determined by unique id as uri param
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $model = $this->getEntity($id);
        if ($model !== null) {
            $model->delete();
            $resource = Json::getResource($this->middleWare, $model, $this->entity);
            Json::outputSerializedData($resource, JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
        }
    }

    /**
     * GET the relationships of this particular Entity
     * @param int $id
     * @param string $relation
     */
    public function relations(int $id, string $relation)
    {
        $item = $this->getEntity($id);
        $resource = Json::getResource($this->middleWare, $item->$relation, $this->entity);
        Json::outputSerializedData($resource);
    }

    private function getEntity(int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, ['id', $id]
        );

        return $obj->first();
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
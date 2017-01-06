<?php
namespace rjapi\extension;

use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\FileManager;
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
    private $relsRemoved = false;

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

        $this->modelEntity = Classes::getModelEntity($this->entity);
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
        $this->setRelationships($json, $this->model->id);

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
        $json = Json::parse($request->getContent());
        $jsonApiAttributes = Json::getAttributes($json);
        $model = $this->getEntity($id);
        foreach ($this->props as $k => $v) {
            // request fields should match Middleware fields
            if (empty($jsonApiAttributes[$k]) === false) {
                $model->$k = $jsonApiAttributes[$k];
            }
        }
        $model->save();
        $this->setRelationships($json, $model->id, true);

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
        }
        Json::outputSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * GET the relationships of this particular Entity
     *
     * @param Request $request
     * @param int $id
     * @param string $relation
     */
    public function relations(Request $request, int $id, string $relation)
    {
        // TODO: check for existence of an entity for $id
        $item = $this->getEntity($id);
        $resource = Json::getRelations($item->$relation, $relation);
        Json::outputSerializedRelations($request, $resource);
    }

    /**
     * POST relationships for specific entity id
     *
     * @param Request $request
     * @param int $id
     * @param string $relation
     */
    public function createRelations(Request $request, int $id, string $relation)
    {
        $json = Json::parse($request->getContent());
        $this->setRelationships($json, $id);
        // set include for relations
        $_GET['include'] = $relation;
        // TODO: check for existence of an entity for $id
        $model = $this->getEntity($id);
        $resource = Json::getResource($this->middleWare, $model, $this->entity);
        Json::outputSerializedData($resource);
    }

    /**
     * PATCH relationships for specific entity id
     *
     * @param Request $request
     * @param int $id
     * @param string $relation
     */
    public function updateRelations(Request $request, int $id, string $relation)
    {
        $json = Json::parse($request->getContent());
        $this->setRelationships($json, $id, true);
        // set include for relations
        $_GET['include'] = $relation;
        // TODO: check for existence of an entity for $id
        $model = $this->getEntity($id);
        $resource = Json::getResource($this->middleWare, $model, $this->entity);
        Json::outputSerializedData($resource);
    }

    /**
     * DELETE relationships for specific entity id
     *
     * @param Request $request JSON API formatted string
     * @param int $id          int id of an entity
     * @param string $relation
     */
    public function deleteRelations(Request $request, int $id, string $relation)
    {
        $json = Json::parse($request->getContent());
        $jsonApiRels = Json::getData($json);
        if (empty($jsonApiRels) === false) {
            $lowEntity = strtolower($this->entity);
            foreach ($jsonApiRels as $index => $val) {
                $rId = $val[RamlInterface::RAML_ID];
                // if pivot file exists then save
                $ucEntity = ucfirst($relation);
                $file = DirsInterface::MODULES_DIR . PhpEntitiesInterface::SLASH
                    . Config::getModuleName() . PhpEntitiesInterface::SLASH .
                    DirsInterface::ENTITIES_DIR . PhpEntitiesInterface::SLASH .
                    $this->entity . $ucEntity . PhpEntitiesInterface::PHP_EXT;
                if (file_exists(PhpEntitiesInterface::SYSTEM_UPDIR . $file)) { // ManyToMany rel
                    $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
                    // clean up old links
                    $this->getModelEntities(
                        $pivotEntity,
                        [[$lowEntity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID => $id,
                          $relation . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID => $rId]]
                    )->delete();
                } else { // OneToOne/Many
                    $relEntity = Classes::getModelEntity($ucEntity);
                    $refModel = new $relEntity();
                    $model = $this->getModelEntities($refModel, [$lowEntity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID, $id]);
                    $model->update([$relation . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID => 0]);
                }
            }
            Json::outputSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
        }
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    private function getEntity(int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [RamlInterface::RAML_ID, $id]
        );

        return $obj->first();
    }

    /**
     * @param string $modelEntity
     * @param int $id
     *
     * @return mixed
     */
    private function getModelEntity($modelEntity, int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [RamlInterface::RAML_ID, $id]
        );

        return $obj->first();
    }

    /**
     * @param string $modelEntity
     * @param array $params
     *
     * @return mixed
     */
    private function getModelEntities($modelEntity, array $params)
    {
        return call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, $params
        );
    }

    /**
     * @param int $count
     * @param int $page
     *
     * @return mixed
     */
    private function getAllEntities(int $count = ModelsInterface::DEFAULT_LIMIT, int $page = 1)
    {
        $from = ($count * $page) - $count;
        $to = $count * $page;
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            [RamlInterface::RAML_ID, ModelsInterface::SQL_DESC]
        );

        return $obj->take($to)->skip($from)->get();
    }

    private function setRelationships(array $json, int $eId, bool $isRemovable = false)
    {
        $jsonApiRels = Json::getRelationships($json);
        if (empty($jsonApiRels) === false) {
            foreach ($jsonApiRels as $entity => $value) {
                if (empty($value[RamlInterface::RAML_DATA][RamlInterface::RAML_ID]) === false) {
                    // if there is only one relationship
                    $rId = $value[RamlInterface::RAML_DATA][RamlInterface::RAML_ID];
                    $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                } else {
                    // if there is an array of relationships
                    foreach ($value[RamlInterface::RAML_DATA] as $index => $val) {
                        $rId = $val[RamlInterface::RAML_ID];
                        $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                    }
                }
            }
        }
    }

    private function saveRelationship($entity, int $eId, int $rId, bool $isRemovable = false)
    {
        $ucEntity = ucfirst($entity);
        $lowEntity = strtolower($this->entity);
        // if pivot file exists then save
        $filePivot = FileManager::getPivotFile($this->entity, $ucEntity);
        $filePivotInverse = FileManager::getPivotFile($ucEntity, $this->entity);
        $pivotExists = file_exists(PhpEntitiesInterface::SYSTEM_UPDIR . $filePivot);
        $pivotInverseExists = file_exists(PhpEntitiesInterface::SYSTEM_UPDIR . $filePivotInverse);
        if ($pivotExists || $pivotInverseExists) { // ManyToMany rel
            $pivotEntity = null;

            if ($pivotExists) {
                $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
            } else if ($pivotInverseExists) {
                $pivotEntity = Classes::getModelEntity($ucEntity . $this->entity);
            }
            if ($isRemovable && $this->relsRemoved === false) {
                // clean up old links
                $this->getModelEntities(
                    $pivotEntity,
                    [$lowEntity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID, $eId]
                )->delete();
                $this->relsRemoved = true;
            }

            $pivot = new $pivotEntity();
            $pivot->{$entity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID} = $rId;
            $pivot->{$lowEntity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID} = $eId;
            $pivot->save();
        } else { // OneToOne
            $relEntity = Classes::getModelEntity($ucEntity);
            $refModel = new $relEntity();
            $model = $this->getModelEntity($refModel, $rId);
            $model->{$lowEntity . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID} = $eId;
            $model->save();
        }
    }
}
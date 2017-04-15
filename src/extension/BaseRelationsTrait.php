<?php
namespace rjapi\extension;

use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use rjapi\blocks\FileManager;
use rjapi\helpers\Classes;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\Json;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

trait BaseRelationsTrait
{
    use BaseModelTrait;
    /**
     * GET the relationships of this particular Entity
     *
     * @param Request $request
     * @param int $id
     * @param string $relation
     */
    public function relations(Request $request, int $id, string $relation)
    {
        $model = $this->getEntity($id);
        if(empty($model))
        {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE => 'Database object ' . $this->entity . ' with $id = ' . $id .
                            ' - not found.',
                    ],
                ]
            );
        }
        $resource = Json::getRelations($model->$relation, $relation);
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
        $model = $this->presetRelations($request, $id, $relation);
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
        $model = $this->presetRelations($request, $id, $relation, true);
        $resource = Json::getResource($this->middleWare, $model, $this->entity);
        Json::outputSerializedData($resource);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param string $relation
     * @param bool $isRemovable
     * @return mixed
     */
    private function presetRelations(Request $request, int $id, string $relation, bool $isRemovable = false)
    {
        $json = Json::decode($request->getContent());
        $this->setRelationships($json, $id, true);
        // set include for relations
        $_GET['include'] = $relation;
        $model = $this->getEntity($id);
        if(empty($model))
        {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE => 'Database object ' . $this->entity . ' with $id = ' . $id .
                            ' - not found.',
                    ],
                ]
            );
        }

        return $model;
    }

    /**
     * DELETE relationships for specific entity id
     *
     * @param Request $request JSON API formatted string
     * @param int $id int id of an entity
     * @param string $relation
     */
    public function deleteRelations(Request $request, int $id, string $relation)
    {
        $json = Json::decode($request->getContent());
        $jsonApiRels = Json::getData($json);
        if(empty($jsonApiRels) === false)
        {
            $lowEntity = strtolower($this->entity);
            foreach($jsonApiRels as $index => $val)
            {
                $rId = $val[RamlInterface::RAML_ID];
                // if pivot file exists then save
                $ucEntity = ucfirst($relation);
                $file = DirsInterface::MODULES_DIR . PhpInterface::SLASH
                    . ConfigHelper::getModuleName() . PhpInterface::SLASH .
                    DirsInterface::ENTITIES_DIR . PhpInterface::SLASH .
                    $this->entity . $ucEntity . PhpInterface::PHP_EXT;
                if(file_exists(PhpInterface::SYSTEM_UPDIR . $file))
                { // ManyToMany rel
                    $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
                    // clean up old links
                    $this->getModelEntities(
                        $pivotEntity,
                        [
                            [
                                $lowEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID => $id,
                                $relation . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID  => $rId,
                            ],
                        ]
                    )->delete();
                }
                else
                { // OneToOne/Many
                    $relEntity = Classes::getModelEntity($ucEntity);
                    $model = $this->getModelEntities(
                        $relEntity, [
                            $lowEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID, $id,
                        ]
                    );
                    $model->update([$relation . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID => 0]);
                }
            }
            Json::outputSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
        }
    }

    /**
     * @param array $json
     * @param int $eId
     * @param bool $isRemovable
     */
    private function setRelationships(array $json, int $eId, bool $isRemovable = false)
    {
        $jsonApiRels = Json::getRelationships($json);
        if(empty($jsonApiRels) === false)
        {
            foreach($jsonApiRels as $entity => $value)
            {
                if(empty($value[RamlInterface::RAML_DATA][RamlInterface::RAML_ID]) === false)
                {
                    // if there is only one relationship
                    $rId = $value[RamlInterface::RAML_DATA][RamlInterface::RAML_ID];
                    $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                }
                else
                {
                    // if there is an array of relationships
                    foreach($value[RamlInterface::RAML_DATA] as $index => $val)
                    {
                        $rId = $val[RamlInterface::RAML_ID];
                        $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                    }
                }
            }
        }
    }

    /**
     * @param      $entity
     * @param int $eId
     * @param int $rId
     * @param bool $isRemovable
     */
    private function saveRelationship($entity, int $eId, int $rId, bool $isRemovable = false)
    {
        $ucEntity = Classes::getClassName($entity);
        $lowEntity = MigrationsHelper::getTableName($this->entity);
        // if pivot file exists then save
        $filePivot = FileManager::getPivotFile($this->entity, $ucEntity);
        $filePivotInverse = FileManager::getPivotFile($ucEntity, $this->entity);
        $pivotExists = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivot);
        $pivotInverseExists = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivotInverse);
        if($pivotExists === true || $pivotInverseExists === true)
        { // ManyToMany rel
            $pivotEntity = null;

            if($pivotExists)
            {
                $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
            }
            else
            {
                if($pivotInverseExists)
                {
                    $pivotEntity = Classes::getModelEntity($ucEntity . $this->entity);
                }
            }

            if($isRemovable === true)
            {
                $this->clearPivotBeforeSave($pivotEntity, $lowEntity, $eId);
            }
            $this->savePivot($pivotEntity, $lowEntity, $entity, $eId, $rId);
        }
        else
        { // OneToOne
            $this->saveModel($ucEntity, $lowEntity, $eId, $rId);
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param int $eId
     */
    private function clearPivotBeforeSave(string $pivotEntity, string $lowEntity, int $eId)
    {
        if($this->relsRemoved === false)
        {
            // clean up old links
            $this->getModelEntities(
                $pivotEntity,
                [$lowEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID, $eId]
            )->delete();
            $this->relsRemoved = true;
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param string $entity
     * @param int $eId
     * @param int $rId
     */
    private function savePivot(string $pivotEntity, string $lowEntity, string $entity, int $eId, int $rId)
    {
        $pivot = new $pivotEntity();
        $pivot->{$entity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID} = $rId;
        $pivot->{$lowEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID} = $eId;
        $pivot->save();
    }

    /**
     * @param string $ucEntity
     * @param string $lowEntity
     * @param int $eId
     * @param int $rId
     */
    private function saveModel(string $ucEntity, string $lowEntity, int $eId, int $rId)
    {
        $relEntity =
            Classes::getModelEntity($ucEntity);
        $model =
            $this->getModelEntity($relEntity, $rId);
        $model->{$lowEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID} = $eId;
        $model->save();
    }
}
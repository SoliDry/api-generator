<?php

namespace SoliDry\Extension;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SoliDry\Blocks\FileManager;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\Json;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;
use SoliDry\Helpers\ConfigHelper as conf;

/**
 * Trait BaseRelationsTrait
 *
 * @package SoliDry\Extension
 *
 * @property Json json
 * @property \SoliDry\Containers\Response response
 */
trait BaseRelationsTrait
{
    use BaseModelTrait;

    /**
     * GET the relationships of this particular Entity
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return Response
     */
    public function relations(Request $request, $id, string $relation): Response
    {
        $model = $this->getEntity($id);
        if (empty($model)) {
            return $this->response->getModelNotFoundError($this->entity, $id);
        }

        return $this->response->getRelations($model->$relation, $relation, $request);
    }

    /**
     * GET the fully represented relationships of this particular Entity
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return Response
     */
    public function related(Request $request, $id, string $relation): Response
    {
        $data = ($request->input(ModelsInterface::PARAM_DATA) === NULL) ? ModelsInterface::DEFAULT_DATA
            : Json::decode(urldecode($request->input(ModelsInterface::PARAM_DATA)));

        $model = $this->getEntity($id);
        if (empty($model)) {
            return $this->response->getModelNotFoundError($this->entity, $id);
        }

        $relEntity = ucfirst($relation);
        $formRequestEntity = $this->getFormRequestEntity(conf::getModuleName(), $relEntity);
        $relFormRequest = new $formRequestEntity();

        $this->response->setFormRequest($relFormRequest);
        $this->response->setEntity($relEntity);

        return $this->response->getRelated($model->$relation, $data);
    }

    /**
     * POST relationships for specific entity id
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return Response
     */
    public function createRelations(Request $request, $id, string $relation): Response
    {
        $model = $this->presetRelations($request, $id, $relation);

        return $this->response->get($model, []);
    }

    /**
     * PATCH relationships for specific entity id
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return Response
     */
    public function updateRelations(Request $request, $id, string $relation): Response
    {
        $model = $this->presetRelations($request, $id, $relation);

        return $this->response->get($model, []);
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return mixed
     */
    private function presetRelations(Request $request, $id, string $relation)
    {
        $json = Json::decode($request->getContent());
        $this->setRelationships($json, $id, true);

        // set include for relations
        $_GET['include'] = $relation;
        $model = $this->getEntity($id);

        if (empty($model)) {
            return $this->response->getModelNotFoundError($this->entity, $id);
        }

        return $model;
    }

    /**
     * DELETE relationships for specific entity id
     *
     * @param Request $request JSON API formatted string
     * @param int|string $id   int id of an entity
     * @param string $relation
     * @return Response
     */
    public function deleteRelations(Request $request, $id, string $relation): Response
    {
        $json = Json::decode($request->getContent());
        $jsonApiRels = Json::getData($json);
        if (empty($jsonApiRels) === false) {
            $lowEntity = strtolower($this->entity);
            foreach ($jsonApiRels as $index => $val) {
                $rId = $val[ApiInterface::RAML_ID];
                // if pivot file exists then save
                $ucEntity = ucfirst($relation);
                $file = DirsInterface::MODULES_DIR . PhpInterface::SLASH
                    . ConfigHelper::getModuleName() . PhpInterface::SLASH .
                    DirsInterface::ENTITIES_DIR . PhpInterface::SLASH .
                    $this->entity . $ucEntity . PhpInterface::PHP_EXT;
                if (file_exists(PhpInterface::SYSTEM_UPDIR . $file)) { // ManyToMany rel
                    $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
                    // clean up old links
                    $this->getModelEntities(
                        $pivotEntity,
                        [
                            [
                                $lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID => $id,
                                $relation . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID  => $rId,
                            ],
                        ]
                    )->delete();
                } else { // OneToOne/Many - note this is always updates one row related to entity e.g.:
                    // find article by id and update tag_id or topic_id
                    $entity = Classes::getModelEntity($this->entity);

                    /** @var \Illuminate\Database\Eloquent\Builder $model */
                    $model = $this->getModelEntities(
                        $entity, [
                            ApiInterface::RAML_ID,
                            $id,
                        ]
                    );

                    $model->update([$relation . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID => 0]);
                }
            }
        }

        return $this->response->getDeleteRelations();
    }

    /**
     * @param array $json
     * @param int|string $eId
     * @param bool $isRemovable
     */
    protected function setRelationships(array $json, $eId, bool $isRemovable = false): void
    {
        $jsonApiRels = Json::getRelationships($json);
        if (empty($jsonApiRels) === false) {
            foreach ($jsonApiRels as $entity => $value) {
                if (empty($value[ApiInterface::RAML_DATA][ApiInterface::RAML_ID]) === false) {
                    // if there is only one relationship
                    $rId = $value[ApiInterface::RAML_DATA][ApiInterface::RAML_ID];
                    $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                } else {
                    // if there is an array of relationships
                    foreach ($value[ApiInterface::RAML_DATA] as $index => $val) {
                        $rId = $val[ApiInterface::RAML_ID];
                        $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                    }
                }
            }
        }
    }

    /**
     * @param      $entity
     * @param int|string $eId
     * @param int|string $rId
     * @param bool $isRemovable
     */
    private function saveRelationship($entity, $eId, $rId, bool $isRemovable = false): void
    {
        $ucEntity = Classes::getClassName($entity);
        $lowEntity = MigrationsHelper::getTableName($this->entity);
        // if pivot file exists then save
        $filePivot = FileManager::getPivotFile($this->entity, $ucEntity);
        $filePivotInverse = FileManager::getPivotFile($ucEntity, $this->entity);
        $pivotExists = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivot);
        $pivotInverseExists = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivotInverse);
        if ($pivotExists === true || $pivotInverseExists === true) { // ManyToMany rel
            $pivotEntity = NULL;

            if ($pivotExists) {
                $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
            } else {
                if ($pivotInverseExists) {
                    $pivotEntity = Classes::getModelEntity($ucEntity . $this->entity);
                }
            }

            if ($isRemovable === true) {
                $this->clearPivotBeforeSave($pivotEntity, $lowEntity, $eId);
            }
            $this->savePivot($pivotEntity, $lowEntity, $entity, $eId, $rId);
        } else { // OneToOne
            $this->saveModel($ucEntity, $lowEntity, $eId, $rId);
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param int|string $eId
     */
    private function clearPivotBeforeSave(string $pivotEntity, string $lowEntity, $eId): void
    {
        if ($this->relsRemoved === false) {
            // clean up old links
            $this->getModelEntities(
                $pivotEntity,
                [$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID, $eId]
            )->delete();
            $this->relsRemoved = true;
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param string $entity
     * @param int|string $eId
     * @param int|string $rId
     */
    private function savePivot(string $pivotEntity, string $lowEntity, string $entity, $eId, $rId): void
    {
        $pivot = new $pivotEntity();
        $pivot->{$entity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $rId;
        $pivot->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $eId;
        $pivot->save();
    }

    /**
     * Saves model with related id from linked table full duplex
     *
     * @param string $ucEntity
     * @param string $lowEntity
     * @param int|string $eId
     * @param int|string $rId
     */
    private function saveModel(string $ucEntity, string $lowEntity, $eId, $rId): void
    {
        $relEntity = Classes::getModelEntity($ucEntity);
        $model = $this->getModelEntity($relEntity, $rId);

        // swap table and field trying to find rels with inverse
        if (!property_exists($model, $lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID)) {
            $ucTmp = $ucEntity;
            $ucEntity = ucfirst($lowEntity);
            $relEntity = Classes::getModelEntity($ucEntity);
            $model = $this->getModelEntity($relEntity, $eId);
            $lowEntity = strtolower($ucTmp);

            $model->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $rId;
            $model->save();

            return;
        }

        $model->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $eId;
        $model->save();
    }
}
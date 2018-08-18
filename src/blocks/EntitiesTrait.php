<?php

namespace rjapi\blocks;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use League\Fractal\Resource\ResourceAbstract;
use rjapi\exceptions\AttributesException;
use rjapi\extension\ApiController;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\ConfigHelper as conf;
use rjapi\helpers\ConfigOptions;
use rjapi\helpers\Json;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class EntitiesTrait
 *
 * @package rjapi\blocks
 * @property ApiController entity
 * @property BaseFormRequest middleWare
 * @property ApiController props
 * @property BaseModel model
 * @property ApiController modelEntity
 * @property ConfigOptions configOptions
 *
 */
trait EntitiesTrait
{
    public function getMiddlewareEntity(string $version, string $object) : string
    {
        return DirsInterface::MODULES_DIR . PhpInterface::BACKSLASH . strtoupper($version) .
            PhpInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpInterface::BACKSLASH .
            $object .
            DefaultInterface::MIDDLEWARE_POSTFIX;
    }

    protected function setEntities() : void
    {
        $this->entity      = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $middlewareEntity  = $this->getMiddlewareEntity(conf::getModuleName(), $this->entity);
        $this->middleWare  = new $middlewareEntity();
        $this->props       = get_object_vars($this->middleWare);
        $this->modelEntity = Classes::getModelEntity($this->entity);
        $this->model       = new $this->modelEntity();
    }

    /**
     * @param array $jsonApiAttributes
     * @return ResourceAbstract
     * @throws \rjapi\exceptions\AttributesException
     */
    protected function saveBulk(array $jsonApiAttributes) : ResourceAbstract
    {
        $meta       = [];
        $collection = new Collection();

        try {
            DB::beginTransaction();
            foreach ($jsonApiAttributes as $jsonObject) {

                $this->model = new $this->modelEntity();

                // FSM initial state check
                if ($this->configOptions->isStateMachine() === true) {
                    $this->checkFsmCreate($jsonObject);
                }

                // spell check
                if ($this->configOptions->isSpellCheck() === true) {
                    $meta[] = $this->spellCheck($jsonObject);
                }

                // fill in model
                foreach ($this->props as $k => $v) {
                    // request fields should match Middleware fields
                    if (isset($jsonObject[$k])) {
                        $this->model->$k = $jsonObject[$k];
                    }
                }

                // set bit mask
                if (true === $this->configOptions->isBitMask()) {
                    $this->setMaskCreate($jsonObject);
                }

                $collection->push($this->model);
                $this->model->save();
                // jwt
                if ($this->configOptions->getIsJwtAction() === true) {
                    $this->createJwtUser(); // !!! model is overridden
                }

                // set bit mask from model -> response
                if (true === $this->configOptions->isBitMask()) {
                    $this->model = $this->setFlagsCreate();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            echo $e->getTraceAsString();
            DB::rollBack();
        }

        return Json::getResource($this->middleWare, $collection, $this->entity, true, $meta);
    }

    /**
     * @param array $jsonApiAttributes
     * @return ResourceAbstract
     * @throws \rjapi\exceptions\AttributesException
     */
    protected function mutateBulk(array $jsonApiAttributes) : ResourceAbstract
    {
        $meta       = [];
        $collection = new Collection();

        try {
            DB::beginTransaction();
            foreach ($jsonApiAttributes as $jsonObject) {

                $model = $this->getEntity($jsonObject[JSONApiInterface::CONTENT_ID]);

                // FSM transition check
                if ($this->configOptions->isStateMachine() === true) {
                    $this->checkFsmUpdate($jsonObject, $model);
                }

                // spell check
                if ($this->configOptions->isSpellCheck() === true) {
                    $meta[] = $this->spellCheck($jsonObject);
                }

                $this->processUpdate($model, $jsonObject);
                $collection->push($model);
                $model->save();

                // set bit mask
                if (true === $this->configOptions->isBitMask()) {
                    $this->setFlagsUpdate($model);
                }

            }
            DB::commit();
        } catch (\PDOException $e) {
            echo $e->getTraceAsString();
            DB::rollBack();
        }

        return Json::getResource($this->middleWare, $collection, $this->entity, true, $meta);
    }

    /**
     * @param array $jsonApiAttributes
     * @throws AttributesException
     */
    public function removeBulk(array $jsonApiAttributes) : void
    {
        try {
            DB::beginTransaction();

            foreach ($jsonApiAttributes as $jsonObject) {
                $model = $this->getEntity($jsonObject[JSONApiInterface::CONTENT_ID]);

                if ($model === null) {
                    DB::rollBack();
                    throw new AttributesException('There is no such id: ' . $jsonObject[JSONApiInterface::CONTENT_ID] . ' or model was already deleted - transaction has been rolled back.');
                }

                $model->delete();
            }

            DB::commit();
        } catch (\PDOException $e) {
            echo $e->getTraceAsString();
            DB::rollBack();
        }
    }

    /**
     * Gets the relations of entity or null
     * @param string $objectName
     *
     * @return mixed
     */
    private function getRelationType(string $objectName)
    {
        if (empty($this->generator->types[$objectName][RamlInterface::RAML_PROPS]
                  [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
        ) {
            return trim(
                $this->generator->types[$objectName][RamlInterface::RAML_PROPS]
                [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
            );
        }

        return null;
    }

    private function setUseSoftDelete() : void
    {
        if ($this->isSoftDelete()) {
            $this->setUse(Classes::getObjectName(SoftDeletes::class), true, true);
        }
    }

    private function setPropSoftDelete() : void
    {
        if ($this->isSoftDelete()) {
            $this->createPropertyArray(ModelsInterface::PROPERTY_DATES, PhpInterface::PHP_MODIFIER_PROTECTED, [ModelsInterface::COLUMN_DEL_AT]);
        }
    }
}
<?php

namespace SoliDry\Blocks;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SoliDry\Exceptions\ErrorHandler;
use SoliDry\Extension\ApiController;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\BaseModel;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\ConfigHelper as conf;
use SoliDry\Helpers\ConfigOptions;
use SoliDry\Helpers\Json;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class EntitiesTrait
 *
 * @package SoliDry\Blocks
 * @property ApiController entity
 * @property BaseFormRequest $formRequest
 * @property ApiController props
 * @property BaseModel model
 * @property ApiController modelEntity
 * @property ConfigOptions configOptions
 * @property Json json
 * @property \SoliDry\Containers\Response response
 */
trait EntitiesTrait
{
    use ErrorHandler;

    /**
     * Gets form request entity fully qualified path
     *
     * @param string $version
     * @param string $object
     * @return string
     */
    public function getFormRequestEntity(string $version, string $object): string
    {
        return DirsInterface::MODULES_DIR . PhpInterface::BACKSLASH . strtoupper($version) .
            PhpInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpInterface::BACKSLASH .
            DirsInterface::FORM_REQUEST_DIR . PhpInterface::BACKSLASH .
            $object .
            DefaultInterface::FORM_REQUEST_POSTFIX;
    }

    /**
     *  Sets all props/entities needed to process request
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \ReflectionException
     */
    protected function setEntities(): void
    {
        $this->entity = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $formRequestEntity = $this->getFormRequestEntity(conf::getModuleName(), $this->entity);

        $this->formRequest = new $formRequestEntity();
        $this->props = get_object_vars($this->formRequest);

        $this->modelEntity = Classes::getModelEntity($this->entity);
        $this->model = new $this->modelEntity();

        $container = Container::getInstance();
        $this->response = $container->make(\SoliDry\Containers\Response::class);
        $this->response->setFormRequest($this->formRequest);
        $this->response->setEntity($this->entity);
    }

    /**
     * Save bulk transactionally, if there are some errors - rollback
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function saveBulk(Request $request): Response
    {
        $meta = [];
        $collection = new Collection();

        $json = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

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
                    // request fields should match FormRequest fields
                    if (isset($jsonObject[$k])) {
                        $this->model->$k = $jsonObject[$k];
                    }
                }

                // set bit mask
                if ($this->configOptions->isBitMask() === true) {
                    $this->setMaskCreate($jsonObject);
                }

                $collection->push($this->model);
                $this->model->save();

                // jwt
                if ($this->configOptions->getIsJwtAction() === true) {
                    $this->createJwtUser(); // !!! model is overridden
                }

                // set bit mask from model -> response
                if ($this->configOptions->isBitMask() === true) {
                    $this->model = $this->setFlagsCreate();
                }
            }

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();

            return $this->getErrorResponse($request, $e);
        }

        return $this->response->get($collection, $meta);
    }

    /**
     * Mutates/Updates a bulk by applying it to transaction/rollback procedure
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \SoliDry\Exceptions\AttributesException
     * @throws \LogicException
     */
    protected function mutateBulk(Request $request): Response
    {
        $meta = [];
        $collection = new Collection();

        $json = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

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
                if ($this->configOptions->isBitMask() === true) {
                    $this->setFlagsUpdate($model);
                }

            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();

            return $this->getErrorResponse($request, $e);
        }

        return $this->response->get($collection, $meta);
    }

    /**
     * Deletes bulk by applying it to transaction/rollback procedure
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function removeBulk(Request $request): Response
    {
        $json = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        try {
            DB::beginTransaction();

            foreach ($jsonApiAttributes as $jsonObject) {
                $model = $this->getEntity($jsonObject[JSONApiInterface::CONTENT_ID]);

                if ($model === null) {
                    DB::rollBack();

                    return $this->response->getModelNotFoundError($this->modelEntity, $jsonObject[JSONApiInterface::CONTENT_ID]);
                }

                $model->delete();
            }

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();

            return $this->getErrorResponse($request, $e);
        }

        return $this->response->removeBulk();
    }

    /**
     * Gets the relations of entity or null
     * @param string $objectName
     *
     * @return mixed
     */
    private function getRelationType(string $objectName)
    {
        if (empty($this->generator->types[$objectName][ApiInterface::RAML_PROPS]
            [ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]) === false
        ) {
            return trim(
                $this->generator->types[$objectName][ApiInterface::RAML_PROPS]
                [ApiInterface::RAML_RELATIONSHIPS][ApiInterface::RAML_TYPE]
            );
        }

        return null;
    }

    /**
     * Sets use stmt for Soft Delete op on model Entity
     *
     * @throws \ReflectionException
     */
    private function setUseSoftDelete(): void
    {
        if ($this->isSoftDelete()) {
            $this->setUse(Classes::getObjectName(SoftDeletes::class), true, true);
        }
    }

    /**
     * Sets property for Soft Delete op on model Entity
     */
    private function setPropSoftDelete(): void
    {
        if ($this->isSoftDelete()) {
            $this->createPropertyArray(ModelsInterface::PROPERTY_DATES, PhpInterface::PHP_MODIFIER_PROTECTED, [ModelsInterface::COLUMN_DEL_AT]);
        }
    }
}
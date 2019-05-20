<?php

namespace SoliDry\Blocks;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Resource\Collection as FractalCollection;
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
use SoliDry\Helpers\Errors;
use SoliDry\Helpers\Json;
use SoliDry\Helpers\JsonApiResponse;
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
    public function getFormRequestEntity(string $version, string $object) : string
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
    protected function setEntities() : void
    {
        $this->entity      = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $formRequestEntity  = $this->getFormRequestEntity(conf::getModuleName(), $this->entity);

        $this->formRequest = new $formRequestEntity();
        $this->props       = get_object_vars($this->formRequest);

        $this->modelEntity = Classes::getModelEntity($this->entity);
        $this->model       = new $this->modelEntity();

        $container = Container::getInstance();
        $this->response = $container->make(\SoliDry\Helpers\Response::class);
        $this->response->setFormRequest($this->formRequest);
        $this->response->setEntity($this->entity);
    }

    /**
     * Save bulk transactionally, if there are some errors - rollback
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \SoliDry\Exceptions\AttributesException
     * @throws \LogicException
     */
    protected function saveBulk(Request $request) : Response
    {
        $meta       = [];
        $collection = new Collection();

        $json              = Json::decode($request->getContent());
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

        $resource = $this->json->setIsCollection(true)
            ->setMeta($meta)->getResource($this->formRequest, $collection, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
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
    protected function mutateBulk(Request $request) : Response
    {
        $meta       = [];
        $collection = new Collection();

        $json              = Json::decode($request->getContent());
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

        $resource = $this->json->setIsCollection(true)
            ->setMeta($meta)->getResource($this->formRequest, $collection, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource));
    }

    /**
     * Deltes bulk by applying it to transaction/rollback procedure
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function removeBulk(Request $request) : Response
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        try {
            DB::beginTransaction();

            foreach ($jsonApiAttributes as $jsonObject) {
                $model = $this->getEntity($jsonObject[JSONApiInterface::CONTENT_ID]);

                if ($model === null) {
                    DB::rollBack();

                    return (new JsonApiResponse())->getResponse(
                        (new Json())->getErrors(
                            (new Errors())->getModelNotFound($this->modelEntity, $jsonObject[JSONApiInterface::CONTENT_ID])),
                        JSONApiInterface::HTTP_RESPONSE_CODE_NOT_FOUND);
                }

                $model->delete();
            }

            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();

            return $this->getErrorResponse($request, $e);
        }

        return (new JsonApiResponse())->getResponse(Json::prepareSerializedData(
            new FractalCollection()), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
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
    private function setUseSoftDelete() : void
    {
        if ($this->isSoftDelete()) {
            $this->setUse(Classes::getObjectName(SoftDeletes::class), true, true);
        }
    }

    /**
     * Sets property for Soft Delete op on model Entity
     */
    private function setPropSoftDelete() : void
    {
        if ($this->isSoftDelete()) {
            $this->createPropertyArray(ModelsInterface::PROPERTY_DATES, PhpInterface::PHP_MODIFIER_PROTECTED, [ModelsInterface::COLUMN_DEL_AT]);
        }
    }
}
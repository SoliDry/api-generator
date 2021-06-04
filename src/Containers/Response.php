<?php

namespace SoliDry\Containers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Errors;
use SoliDry\Helpers\Json;
use SoliDry\Helpers\JsonApiResponse;
use SoliDry\Helpers\SqlOptions;
use League\Fractal\Resource\Collection as FractalCollection;

/**
 * Class Response
 *
 * @package SoliDry\Helpers
 *
 * @property SqlOptions sqlOptions
 */
class Response
{
    private Json $json;
    private Errors $errors;

    private $formRequest;
    private $entity;
    private $method;

    /**
     * Response constructor.
     *
     * @param Json $json
     * @param Errors $errors
     */
    public function __construct(Json $json, Errors $errors)
    {
        $this->json = $json;
        $this->errors = $errors;
    }

    /**
     * @uses getIndex
     * @uses getView
     * @uses getCreate
     * @uses getUpdate
     * @uses getCreateRelations
     * @uses getUpdateRelations
     * @uses getUpdateBulk
     * @uses getCreateBulk
     *
     * @param $data
     * @param array $meta
     * @return mixed
     */
    public function get($data, array $meta)
    {
        return $this->{'get' . ucfirst($this->method)}($data, $meta);
    }

    /**
     * @param Collection $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getIndex($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setIsCollection(true)->setMeta($meta)
            ->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource, $this->sqlOptions->getData()));
    }

    /**
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getView($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource,  $this->sqlOptions->getData()));
    }

    /**
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getCreate($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getUpdate($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource));
    }

    /**
     * Gets an output for relations
     *
     * @param $relationModel
     * @param string $entity
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRelations($relationModel, string $entity, Request $request): \Illuminate\Http\Response
    {
        $resource = Json::getRelations($relationModel, $entity);

        return $this->getResponse(Json::prepareSerializedRelations($request, $resource));
    }

    /**
     * Gets an output for createRelations
     *
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getCreateRelations($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * Gets an output for updateRelations
     *
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getUpdateRelations($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource));
    }

    /**
     * Gets an output for deleteRelations
     *
     * @return \Illuminate\Http\Response
     */
    public function getDeleteRelations():  \Illuminate\Http\Response
    {
        return $this->getResponse(Json::prepareSerializedData(new FractalCollection()), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }


    /**
     * Gets an output for createBulk
     *
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getCreateBulk($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setIsCollection(true)
            ->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * Gets an output for updateBulk
     *
     * @param $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getUpdateBulk($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setIsCollection(true)
            ->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource));
    }

    /**
     * Gets an output for removeBulk
     *
     * @return \Illuminate\Http\Response
     */
    public function removeBulk(): \Illuminate\Http\Response
    {
        return $this->getResponse(Json::prepareSerializedData(
            new FractalCollection()), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * Gets an output for related
     *
     * @param BaseFormRequest $relationData
     * @param $data
     * @return \Illuminate\Http\Response
     */
    public function getRelated($relationData, $data): \Illuminate\Http\Response
    {
        $this->json->setIsCollection($relationData instanceof  Collection);
        $resource = $this->json->getResource($this->formRequest, $relationData, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource, $data));
    }

    /**
     * Gets an output for model not found error
     *
     * @param string $entity
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function getModelNotFoundError(string $entity, $id): \Illuminate\Http\Response
    {
        return $this->getResponse($this->json->getErrors($this->errors->getModelNotFound($entity, $id)),
            JSONApiInterface::HTTP_RESPONSE_CODE_NOT_FOUND);
    }

    /**
     * @param mixed $formRequest
     * @return Response
     */
    public function setFormRequest($formRequest): Response
    {
        $this->formRequest = $formRequest;

        return $this;
    }

    /**
     * @param mixed $entity
     * @return Response
     */
    public function setEntity($entity): Response
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param mixed $sqlOptions
     * @return Response
     */
    public function setSqlOptions(SqlOptions $sqlOptions): Response
    {
        $this->sqlOptions = $sqlOptions;

        return $this;
    }

    /**
     * Prepares Response object to return with particular http response, headers and body
     *
     * @param string $json
     * @param int $responseCode
     * @return \Illuminate\Http\Response
     */
    public function getResponse(string $json, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK) : \Illuminate\Http\Response
    {
        return (new JsonApiResponse())->getResponse($json, $responseCode);
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }
}
<?php

namespace SoliDry\Containers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use SoliDry\Extension\JSONApiInterface;
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
    private $json;
    private $formRequest;
    private $entity;
    private $sqlOptions;
    private $method;

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

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
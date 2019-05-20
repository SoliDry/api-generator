<?php

namespace SoliDry\Helpers;

use Illuminate\Database\Eloquent\Collection;
use SoliDry\Extension\JSONApiInterface;

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

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * @param Collection $data
     * @param array $meta
     * @return \Illuminate\Http\Response
     */
    public function getIndexResponse($data, array $meta): \Illuminate\Http\Response
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
    public function getViewResponse($data, array $meta): \Illuminate\Http\Response
    {
        $resource = $this->json->setMeta($meta)->getResource($this->formRequest, $data, $this->entity);

        return $this->getResponse(Json::prepareSerializedData($resource,  $this->sqlOptions->getData()));
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
}
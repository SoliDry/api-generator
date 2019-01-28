<?php

namespace rjapi\extension;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use rjapi\exceptions\HeadersException;
use rjapi\helpers\Json;
use rjapi\helpers\Request as RequestHelper;
use rjapi\types\ErrorsInterface;

class BaseController extends ApiController
{

    /**
     * BaseController constructor.
     * @param Route $route
     * @throws HeadersException
     */
    public function __construct(Route $route)
    {
        parent::__construct($route);
        if (in_array($route->getActionMethod(), self::AVAILABLE_BULKS, true) && RequestHelper::isExt(request(), self::EXT_BULK) === false) {
            throw new HeadersException(ErrorsInterface::JSON_API_ERRORS[ErrorsInterface::HTTP_CODE_BULK_EXT_ERROR], ErrorsInterface::HTTP_CODE_BULK_EXT_ERROR);
        }
    }

    /**
     * Creates bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \rjapi\exceptions\AttributesException
     */
    public function createBulk(Request $request) : Response
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        return $this->getResponse(Json::prepareSerializedData($this->saveBulk($jsonApiAttributes), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED));
    }

    /**
     * Update bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \rjapi\exceptions\AttributesException
     */
    public function updateBulk(Request $request) : Response
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        return $this->getResponse(Json::prepareSerializedData($this->mutateBulk($jsonApiAttributes)));
    }

    /**
     * Delete bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     */
    public function deleteBulk(Request $request) : Response
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        $this->removeBulk($jsonApiAttributes);

        return $this->getResponse(Json::prepareSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT));
    }
}
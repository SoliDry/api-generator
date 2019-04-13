<?php

namespace SoliDry\Extension;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use SoliDry\Exceptions\HeadersException;
use SoliDry\Helpers\Json;
use SoliDry\Helpers\Request as RequestHelper;
use SoliDry\Types\ErrorsInterface;

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
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function createBulk(Request $request) : Response
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        return $this->getResponse(Json::prepareSerializedData($this->saveBulk($jsonApiAttributes)), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * Update bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \SoliDry\Exceptions\AttributesException
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
        return $this->removeBulk($request);
    }
}
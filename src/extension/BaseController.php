<?php

namespace rjapi\extension;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use League\Fractal\Resource\Collection;
use rjapi\exceptions\HeadersException;
use rjapi\helpers\Json;
use rjapi\helpers\Request as RequestHelper;

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
            throw new HeadersException('There is no ' . self::EXT_BULK . ' value in ' . self::EXT . ' key of ' . self::CONTENT_TYPE_KEY . ' header');
        }
    }

    /**
     * Creates bulk of items in transaction mode
     *
     * @param Request $request
     * @throws \LogicException
     * @throws \rjapi\exceptions\AttributesException
     */
    public function createBulk(Request $request)
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        Json::outputSerializedData($this->saveBulk($jsonApiAttributes), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);
    }

    /**
     * Update bulk of items in transaction mode
     *
     * @param Request $request
     * @throws \LogicException
     * @throws \rjapi\exceptions\AttributesException
     */
    public function updateBulk(Request $request)
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        Json::outputSerializedData($this->mutateBulk($jsonApiAttributes));
    }

    /**
     * Delete bulk of items in transaction mode
     *
     * @param Request $request
     * @throws \LogicException
     */
    public function deleteBulk(Request $request)
    {
        $json              = Json::decode($request->getContent());
        $jsonApiAttributes = Json::getBulkAttributes($json);

        $this->removeBulk($jsonApiAttributes);

        Json::outputSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }
}
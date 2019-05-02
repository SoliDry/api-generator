<?php

namespace SoliDry\Extension;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use SoliDry\Exceptions\HeadersException;
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
        if (\in_array($route->getActionMethod(), self::AVAILABLE_BULKS, true) && RequestHelper::isExt(request(), self::EXT_BULK) === false) {
            throw new HeadersException(ErrorsInterface::JSON_API_ERRORS[ErrorsInterface::HTTP_CODE_BULK_EXT_ERROR], ErrorsInterface::HTTP_CODE_BULK_EXT_ERROR);
        }
    }

    /**
     * Creates bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function createBulk(Request $request) : Response
    {
        return $this->saveBulk($request);
    }

    /**
     * Update bulk of items in transaction mode
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function updateBulk(Request $request) : Response
    {
        return $this->mutateBulk($request);
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
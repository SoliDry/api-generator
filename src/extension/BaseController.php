<?php

namespace rjapi\extension;

use Illuminate\Http\Request;
use rjapi\exceptions\HeadersException;
use rjapi\helpers\Request as RequestHelper;

class BaseController extends ApiController
{

    /**
     * @param Request $request
     * @throws \rjapi\exceptions\AttributesException
     */
    public function create(Request $request)
    {
        if (RequestHelper::isExt($request, self::EXT_BULK)) {
            // todo: impl create bulk
        } else {
            parent::create($request);
        }
    }

    /**
     * @param Request $request
     * @throws HeadersException
     */
    public function updateBulk(Request $request)
    {
        if (RequestHelper::isExt($request, self::EXT_BULK) === false) {
            throw new HeadersException('There is no ' . self::EXT_BULK . ' value in ' . self::SUPPORTED_EXT . ' key of ' . self::CONTENT_TYPE_KEY);
        }
        // todo: impl update bulk
    }

    /**
     * @param Request $request
     * @throws HeadersException
     */
    public function deleteBulk(Request $request)
    {
        if (RequestHelper::isExt($request, self::EXT_BULK) === false) {
            throw new HeadersException('There is no ' . self::EXT_BULK . ' value in ' . self::SUPPORTED_EXT . ' key of ' . self::CONTENT_TYPE_KEY);
        }
        // todo: impl delete bulk
    }
}
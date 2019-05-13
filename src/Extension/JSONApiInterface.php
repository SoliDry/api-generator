<?php

namespace SoliDry\Extension;

use Illuminate\Http\Request;

/**
 * Interface JSONApiInterface
 * @package SoliDry\Extension
 */
interface JSONApiInterface
{
    // all bulks methods available
    public const AVAILABLE_BULKS = [
        'createBulk',
        'updateBulk',
        'deleteBulk',
    ];

    public const CONTENT_TYPE_KEY = 'Content-Type';
    public const SUPPORTED_EXT    = 'supported-ext';
    public const EXT              = 'ext';
    public const EXT_BULK         = 'bulk';

    public const HEADER_CONTENT_TYPE       = 'Content-Type: ';
    public const HEADER_CONTENT_TYPE_VALUE = 'application/vnd.api+json; supported-ext="bulk"';
    public const STANDARD_HEADERS          = [
        self::CONTENT_TYPE_KEY => self::HEADER_CONTENT_TYPE_VALUE,
    ];

    public const PARAM_ACCESS_TOKEN     = 'access_token';
    public const CLASS_API_ACCESS_TOKEN = 'ApiAccessToken';

    public const URI_METHOD_INDEX       = 'index';
    public const URI_METHOD_VIEW        = 'view';
    public const URI_METHOD_CREATE      = 'create';
    public const URI_METHOD_UPDATE      = 'update';
    public const URI_METHOD_DELETE      = 'delete';
    public const URI_METHOD_RELATIONS   = 'relations';
    public const URI_METHOD_RELATED     = 'related';
    public const URI_METHOD_CREATE_BULK = 'createBulk';
    public const URI_METHOD_UPDATE_BULK = 'updateBulk';
    public const URI_METHOD_DELETE_BULK = 'deleteBulk';
    public const URI_METHOD_OPTIONS     = 'options';

    // JSON API supported responses
    public const HTTP_RESPONSE_CODE_OK               = 200;
    public const HTTP_RESPONSE_CODE_CREATED          = 201;
    public const HTTP_RESPONSE_CODE_ACCEPTED         = 202;
    public const HTTP_RESPONSE_CODE_FORBIDDEN        = 203;
    public const HTTP_RESPONSE_CODE_NO_CONTENT       = 204;
    public const HTTP_RESPONSE_CODE_ACCESS_FORBIDDEN = 403;
    public const HTTP_RESPONSE_CODE_NOT_FOUND        = 404;

    public const FORBIDDEN = 'Forbidden';

    public const CONTENT_LINKS      = 'links';
    public const CONTENT_SELF       = 'self';
    public const CONTENT_RELATED    = 'related';
    public const CONTENT_DATA       = 'data';
    public const CONTENT_TYPE       = 'type';
    public const CONTENT_ID         = 'id';
    public const CONTENT_ERRORS     = 'errors';
    public const CONTENT_ATTRIBUTES = 'attributes';
    public const META_TREE          = 'tree';
    public const PAGINATION         = 'pagination';

    public const EXIT_STATUS_SUCCESS = 0;
    public const EXIT_STATUS_ERROR   = 1;

    public const ERROR_TITLE  = 'title';
    public const ERROR_DETAIL = 'detail';

    public const ENCODE_DEPTH_1024 = 1024;

    // methods JSON-API must implement
    public function index(Request $request);

    public function view(Request $request, $id);

    public function create(Request $request);

    public function update(Request $request, $id);

    public function delete(Request $request, $id);
}
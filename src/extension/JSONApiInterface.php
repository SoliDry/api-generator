<?php
namespace rjapi\extension;

use Illuminate\Http\Request;

interface JSONApiInterface
{
    public const CONTENT_TYPE_KEY = 'Content-Type';
    public const SUPPORTED_EXT    = 'supported-ext';
    public const EXT_BULK         = 'bulk';

    public const HEADER_CONTENT_TYPE       = 'Content-Type: ';
    public const HEADER_CONTENT_TYPE_VALUE = 'application/vnd.api+json; supported-ext="bulk"';

    public const PARAM_ACCESS_TOKEN     = 'access_token';
    public const CLASS_API_ACCESS_TOKEN = 'ApiAccessToken';

    public const URI_METHOD_INDEX     = 'index';
    public const URI_METHOD_VIEW      = 'view';
    public const URI_METHOD_CREATE    = 'create';
    public const URI_METHOD_UPDATE    = 'update';
    public const URI_METHOD_DELETE    = 'delete';
    public const URI_METHOD_RELATIONS = 'relations';

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
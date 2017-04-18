<?php
namespace rjapi\extension;

interface JSONApiInterface
{
    const HEADER_CONTENT_TYPE       = 'Content-Type: ';
    const HEADER_CONTENT_TYPE_VALUE = 'application/vnd.api+json';

    const PARAM_ACCESS_TOKEN     = 'access_token';
    const CLASS_API_ACCESS_TOKEN = 'ApiAccessToken';

    const URI_METHOD_INDEX     = 'index';
    const URI_METHOD_VIEW      = 'view';
    const URI_METHOD_CREATE    = 'create';
    const URI_METHOD_UPDATE    = 'update';
    const URI_METHOD_DELETE    = 'delete';
    const URI_METHOD_RELATIONS = 'relations';

    // JSON API supported responses
    const HTTP_RESPONSE_CODE_OK               = 200;
    const HTTP_RESPONSE_CODE_CREATED          = 201;
    const HTTP_RESPONSE_CODE_ACCEPTED         = 202;
    const HTTP_RESPONSE_CODE_FORBIDDEN        = 203;
    const HTTP_RESPONSE_CODE_NO_CONTENT       = 204;
    const HTTP_RESPONSE_CODE_ACCESS_FORBIDDEN = 403;
    const HTTP_RESPONSE_CODE_NOT_FOUND        = 404;

    const FORBIDDEN = 'Forbidden';

    const CONTENT_LINKS      = 'links';
    const CONTENT_SELF       = 'self';
    const CONTENT_RELATED    = 'related';
    const CONTENT_DATA       = 'data';
    const CONTENT_TYPE       = 'type';
    const CONTENT_ID         = 'id';
    const CONTENT_ERRORS     = 'errors';
    const CONTENT_ATTRIBUTES = 'attributes';
    const META_TREE          = 'tree';

    const EXIT_STATUS_SUCCESS = 0;
    const EXIT_STATUS_ERROR   = 1;

    const ERROR_TITLE  = 'title';
    const ERROR_DETAIL = 'detail';

    const ENCODE_DEPTH_1024 = 1024;
}
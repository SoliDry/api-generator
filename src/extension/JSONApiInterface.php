<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 02/11/2016
 * Time: 18:06
 */

namespace rjapi\extension;

interface JSONApiInterface
{
    const HEADER_CONTENT_TYPE       = 'Content-Type: ';
    const HEADER_CONTENT_TYPE_VALUE = 'application/vnd.api+json';

    const URI_METHOD_INDEX     = 'index';
    const URI_METHOD_VIEW      = 'view';
    const URI_METHOD_CREATE    = 'create';
    const URI_METHOD_UPDATE    = 'update';
    const URI_METHOD_DELETE    = 'delete';
    const URI_METHOD_RELATIONS = 'relations';

    // JSON API supported responses
    const HTTP_RESPONSE_CODE_OK         = 200;
    const HTTP_RESPONSE_CODE_CREATED    = 201;
    const HTTP_RESPONSE_CODE_ACCEPTED   = 202;
    const HTTP_RESPONSE_CODE_FORBIDDEN  = 203;
    const HTTP_RESPONSE_CODE_NO_CONTENT = 204;
    const HTTP_RESPONSE_CODE_NOT_FOUND  = 404;

    const CONTENT_LINKS      = 'links';
    const CONTENT_SELF       = 'self';
    const CONTENT_RELATED    = 'related';
    const CONTENT_DATA       = 'data';
    const CONTENT_TYPE       = 'type';
    const CONTENT_ID         = 'id';
    const CONTENT_ERRORS     = 'errors';
    const CONTENT_ATTRIBUTES = 'attributes';

    const EXIT_STATUS_SUCCESS = 0;
    const EXIT_STATUS_ERROR   = 1;

    const ERROR_TITLE  = 'title';
    const ERROR_DETAIL = 'detail';
}
<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 02/11/2016
 * Time: 18:06
 */

namespace rjapi\extension;

interface HTTPMethodsInterface
{
    const HTTP_METHOD_GET     = 'GET';
    const HTTP_METHOD_POST    = 'POST';
    const HTTP_METHOD_DELETE  = 'DELETE';
    const HTTP_METHOD_PATCH   = 'PATCH';
    const HTTP_METHOD_HEAD    = 'HEAD';
    const HTTP_METHOD_OPTIONS = 'OPTIONS';

    const URI_METHOD_INDEX  = 'index';
    const URI_METHOD_VIEW   = 'view';
    const URI_METHOD_CREATE = 'create';
    const URI_METHOD_UPDATE = 'update';
    const URI_METHOD_DELETE = 'delete';

    // JSON API supported responses
    const HTTP_RESPONSE_CODE_OK         = 200;
    const HTTP_RESPONSE_CODE_CREATED    = 201;
    const HTTP_RESPONSE_CODE_ACCEPTED   = 202;
    const HTTP_RESPONSE_CODE_FORBIDDEN  = 203;
    const HTTP_RESPONSE_CODE_NO_CONTENT = 204;
    const HTTP_RESPONSE_CODE_NOT_FOUND  = 404;

}
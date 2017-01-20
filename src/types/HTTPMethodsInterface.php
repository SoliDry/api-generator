<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 02/11/2016
 * Time: 18:06
 */

namespace rjapi\types;

interface HTTPMethodsInterface
{
    const HTTP_METHOD_GET     = 'GET';
    const HTTP_METHOD_POST    = 'POST';
    const HTTP_METHOD_DELETE  = 'DELETE';
    const HTTP_METHOD_PATCH   = 'PATCH';
    const HTTP_METHOD_HEAD    = 'HEAD';
    const HTTP_METHOD_OPTIONS = 'OPTIONS';
}
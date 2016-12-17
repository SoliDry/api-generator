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
    const HTTP_METHOD_GET    = 'view';
    const HTTP_METHOD_POST   = 'create';
    const HTTP_METHOD_DELETE = 'delete';
    const HTTP_METHOD_PATCH  = 'update';
    const HTTP_METHOD_INDEX  = 'index';
}
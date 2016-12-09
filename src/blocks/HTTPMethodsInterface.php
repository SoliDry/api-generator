<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 02/11/2016
 * Time: 18:06
 */

namespace rjapi\blocks;

interface HTTPMethodsInterface
{
    const HTTP_METHOD_GET    = 'View';
    const HTTP_METHOD_POST   = 'Create';
    const HTTP_METHOD_DELETE = 'Delete';
    const HTTP_METHOD_PATCH  = 'Update';
    const HTTP_METHOD_INDEX  = 'Index';
}
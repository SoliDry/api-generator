<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 19/12/2016
 * Time: 18:07
 */

namespace rjapi\blocks;

interface RoutesInterface
{
    const CLASS_ROUTE = 'Route';

    const METHOD_GROUP  = 'group';
    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PATCH  = 'patch';
    const METHOD_DELETE = 'delete';

    const ROUTES_FILE = 'routes';
}
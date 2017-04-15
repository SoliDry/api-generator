<?php
namespace rjapi\types;

interface RoutesInterface
{
    const CLASS_ROUTE = 'Route';

    const METHOD_GROUP  = 'group';
    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PATCH  = 'patch';
    const METHOD_DELETE = 'delete';

    const ROUTES_FILE_NAME = 'routes';
}
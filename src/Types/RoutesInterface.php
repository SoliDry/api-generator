<?php

namespace SoliDry\Types;

interface RoutesInterface
{
    public const CLASS_ROUTE = 'Route';

    public const METHOD_GROUP   = 'group';
    public const METHOD_GET     = 'get';
    public const METHOD_POST    = 'post';
    public const METHOD_PATCH   = 'patch';
    public const METHOD_DELETE  = 'delete';
    public const METHOD_OPTIONS = 'options';

    public const ROUTES_DIR = 'Routes';
    public const ROUTES_FILE_NAME = 'api';
}
<?php
namespace rjapi\types;

interface HTTPMethodsInterface
{
    public const HTTP_11 = 'HTTP/1.1';

    public const HTTP_METHOD_GET     = 'GET';
    public const HTTP_METHOD_POST    = 'POST';
    public const HTTP_METHOD_DELETE  = 'DELETE';
    public const HTTP_METHOD_PATCH   = 'PATCH';
    public const HTTP_METHOD_HEAD    = 'HEAD';
    public const HTTP_METHOD_OPTIONS = 'OPTIONS';

    public const HTTP_METHODS_AVAILABLE = 'HEAD,GET,POST,PATCH,DELETE,OPTIONS';
}
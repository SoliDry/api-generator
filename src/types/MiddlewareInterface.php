<?php
namespace rjapi\types;


class MiddlewareInterface
{
    const METHOD_HANDLE = 'handle';
    const METHOD_PARAM_REQUEST = 'request';
    const METHOD_PARAM_NEXT = 'next';
}
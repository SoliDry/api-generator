<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20.01.17
 * Time: 7:11
 */

namespace rjapi\types;


class MiddlewareInterface
{
    const METHOD_HANDLE = 'handle';
    const METHOD_PARAM_REQUEST = 'request';
    const METHOD_PARAM_NEXT = 'next';
}
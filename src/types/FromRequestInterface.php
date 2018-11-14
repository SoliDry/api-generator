<?php
namespace rjapi\types;


interface FromRequestInterface
{
    public const METHOD_HANDLE = 'handle';
    public const METHOD_PARAM_REQUEST = 'request';
    public const METHOD_PARAM_NEXT = 'next';
}
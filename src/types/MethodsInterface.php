<?php
namespace rjapi\types;


interface MethodsInterface
{
    // Laravel`s methods
    public const RULES     = 'rules';
    public const RELATIONS = 'relations';
    public const AUTHORIZE = 'authorize';
    public const HANDLE    = 'handle';
    public const CONFIG    = 'config';
    // php native methods
    public const HEADER    = 'header';
}
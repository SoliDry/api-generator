<?php
namespace rjapi\types;


interface MethodsInterface
{
    // Laravel`s methods
    const RULES     = 'rules';
    const RELATIONS = 'relations';
    const AUTHORIZE = 'authorize';
    const HANDLE    = 'handle';
    const CONFIG    = 'config';
    // php native methods
    const HEADER    = 'header';
}
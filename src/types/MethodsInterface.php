<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20.01.17
 * Time: 7:33
 */

namespace rjapi\types;


class MethodsInterface
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
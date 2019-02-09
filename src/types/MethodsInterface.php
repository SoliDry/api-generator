<?php

namespace SoliDry\Types;


interface MethodsInterface
{
    // Laravel`s methods
    public const RULES     = 'rules';
    public const RELATIONS = 'relations';
    public const AUTHORIZE = 'authorize';
    public const HANDLE    = 'handle';
    public const CONFIG    = 'config';
    // php native methods
    public const HEADER = 'header';
    // test methods
    public const TEST_BEFORE = '_before';
    public const TEST_AFTER  = '_after';
    // json-api methods
    public const INDEX  = 'index';
    public const VIEW   = 'view';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}
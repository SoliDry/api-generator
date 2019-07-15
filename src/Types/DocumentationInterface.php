<?php

namespace SoliDry\Types;


interface DocumentationInterface
{

    public const OA_INFO    = '@OA\Info';
    public const OA_CONTACT = '@OA\Contact';
    public const OA_LICENSE = '@OA\License';
    public const OA_GET     = '@OA\Get';
    public const OA_POST    = '@OA\Post';
    public const OA_PATCH   = '@OA\Patch';
    public const OA_DELETE  = '@OA\Delete';


    public const OA_PARAMETER    = '@OA\Parameter';
    public const OA_SCHEMA       = '@OA\Schema';
    public const OA_REQUEST_BODY = '@OA\RequestBody';
    public const OA_RESPONSE     = '@OA\Response';
    public const OA_JSON_CONTENT = '@OA\JsonContent';
    public const OA_MEDIA_TYPE   = '@OA\MediaType';

    public const PATH        = 'path';
    public const SUMMARY     = 'summary';
    public const TAGS        = 'tags';
    public const DESCRIPTION = 'description';
    public const RESPONSE    = 'response';
    public const IN          = 'in';
    public const NAME        = 'name';
    public const REQUIRED    = 'required';
}
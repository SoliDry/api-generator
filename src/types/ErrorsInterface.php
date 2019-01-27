<?php

namespace rjapi\types;


use rjapi\extension\JSONApiInterface;

interface ErrorsInterface
{
    // http errors
    public const HTTP_CODE_BULK_EXT_ERROR = 112;
    public const HTTP_CODE_FSM_ATTR       = 121;
    public const HTTP_CODE_FSM_INIT_ATTR  = 122;

    public const JSON_API_ERRORS = [
        self::HTTP_CODE_BULK_EXT_ERROR => 'There is no ' . JSONApiInterface::EXT_BULK . ' value in ' . JSONApiInterface::EXT . ' key of ' . JSONApiInterface::CONTENT_TYPE_KEY . ' header',
        self::HTTP_CODE_FSM_ATTR       => 'There should be "states" element filled in with FSM.',
    ];

    // console errors
    public const CODE_FOREIGN_KEY = 11;

    public const CONSOLE_ERRORS = [
        self::CODE_FOREIGN_KEY => 'There must be references and on attributes for foreign key construction.',
    ];
}
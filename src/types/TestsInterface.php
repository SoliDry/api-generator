<?php

namespace rjapi\types;


interface TestsInterface
{
    public const TEST_WORD = 'test';

    public const FUNCTIONAL_TESTER = 'FunctionalTester';
    public const PARAM_I           = 'I';
    public const TRY               = 'try';

    // codeception methods
    public const IM_GOING_TO = 'amGoingTo';
    public const SEND_GET    = 'sendGET';
    public const SEND_POST   = 'sendPOST';
    public const SEND_PATCH  = 'sendPATCH';
    public const SEND_DELETE = 'sendDELETE';

    public const SEE_RESP_IS_JSON  = 'seeResponseIsJson';
    public const SEE_RESP_CONTAINS = 'seeResponseContains';
}
<?php

namespace rjapi\exceptions;

use Illuminate\Http\JsonResponse;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Json;

class BaseException extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        parent::__toString();
        return Json::outputErrors([
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'file'    => $this->getFile(),
            'line'    => $this->getLine(),
        ], true);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function render($request) : JsonResponse
    {
        return response()->json(
            [
                JSONApiInterface::CONTENT_ERRORS => [
                    [
                        'code'    => $this->getCode(),
                        'message' => $this->getMessage(),
                        'file'    => $this->getFile(),
                        'line'    => $this->getLine(),
                        'uri'     => $request->getUri(),
                        'meta'    => $this->getTraceAsString(),
                    ],
                ]
            ]
        );
    }
}

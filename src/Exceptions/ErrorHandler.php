<?php

namespace SoliDry\Exceptions;


use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Json;

trait ErrorHandler
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Exception $e
     * @return JsonResponse
     */
    private function prepareRender($request, Exception $e) : JsonResponse
    {
        return response()->json(
            [
                JSONApiInterface::CONTENT_ERRORS => [
                    [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'uri'     => $request->getUri(),
                        'meta'    => $e->getTraceAsString(),
                    ],
                ]
            ]
        );
    }

    /**
     * Gets error from any Request and *Exception
     *
     * @param \Illuminate\Http\Request $request
     * @param Exception $e
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function getErrorResponse($request, Exception $e) : Response
    {
        return response(Json::encode(
            [
                JSONApiInterface::CONTENT_ERRORS => [
                    [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'uri'     => $request->getUri(),
                        'meta'    => $e->getTraceAsString(),
                    ],
                ]
            ]
        ))->withHeaders(JSONApiInterface::STANDARD_HEADERS)->setStatusCode(JSONApiInterface::HTTP_RESPONSE_CODE_NOT_FOUND);
    }

    /**
     * Render an exception as a JSON API error response.
     *
     * @param Request $request
     * @param Exception $e
     * @return JsonResponse
     */
    public function renderJsonApi(Request $request, Exception $e): JsonResponse
    {
        return $this->prepareRender($request, $e);
    }
}
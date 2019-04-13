<?php

namespace SoliDry\Helpers;


use SoliDry\Extension\JSONApiInterface;
use Illuminate\Http\Response;

class JsonApiResponse
{

    /**
     * Prepares Response object to return with particular http response, headers and body
     *
     * @param string $json
     * @param int $responseCode
     * @return Response
     */
    public function getResponse(string $json, int $responseCode = JSONApiInterface::HTTP_RESPONSE_CODE_OK) : Response
    {
        if ($responseCode === JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT) {
            // ! it is important to pass empty string here, otherwise there will be Exception from ResponseFactory
            // seems like a bug in Laravel - either don't have time to report them and don't like their PR rules
            return response('')->withHeaders(JSONApiInterface::STANDARD_HEADERS)->setStatusCode($responseCode);
        }

        return response($json)->withHeaders(JSONApiInterface::STANDARD_HEADERS)->setStatusCode($responseCode);
    }
}
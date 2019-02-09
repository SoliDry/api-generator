<?php
namespace SoliDry\Extension;

use Closure;
use Lcobucci\JWT\Parser;
use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\Jwt;
use SoliDry\Types\ConfigInterface;

class BaseJwt
{
    public function handle($request, Closure $next)
    {
        if(ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ENABLED) === true) {
            if(empty($request->jwt)) {
                die('JWT token required.');
            }
            $token = (new Parser())->parse((string)$request->jwt);
            if(Jwt::verify($token, $token->getHeader('jti')) === false) {
                header('HTTP/1.1 403 Forbidden');
                die('Access forbidden.');
            }
        }

        return $next($request);
    }
}
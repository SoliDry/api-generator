<?php
namespace rjapi\extension;

use Closure;
use Lcobucci\JWT\Parser;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\Jwt;
use rjapi\types\ConfigInterface;

class BaseJwt
{
    public function handle($request, Closure $next)
    {
        if(ConfigHelper::getJwtParam(ConfigInterface::ENABLED) === true)
        {
            if(empty($request->jwt))
            {
                die('JWT token required.');
            }
            $token = (new Parser())->parse((string)$request->jwt);
            if(Jwt::verify($token, $token->getHeader('jti')) === false)
            {
                header('HTTP/1.1 403 Forbidden');
                die('Access forbidden.');
            }
        }

        return $next($request);
    }
}
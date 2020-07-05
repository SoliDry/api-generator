<?php
namespace SoliDry\Extension;

use Closure;
use Lcobucci\JWT\Parser;
use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\Jwt;
use SoliDry\Types\ConfigInterface;

/**
 * Class BaseJwt
 * @package SoliDry\Extension
 */
class BaseJwt
{
    /**
     * Verifies jwt token on configured requests
     * @example
     * 'jwt'=> [
     *   'enabled' => true,
     *   'table' => 'user',
     *   'activate' => 30,
     *   'expires' => 3600,
     * ],
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ENABLED) === true) {
            if(empty($request->jwt)) {
                die('JWT token required.');
            }
            $token = (new Parser())->parse((string)$request->jwt);
            if(Jwt::verify($token) === false) {
                header('HTTP/1.1 403 Forbidden');
                die('Access forbidden.');
            }
        }

        return $next($request);
    }
}
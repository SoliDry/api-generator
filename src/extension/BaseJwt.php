<?php

namespace rjapi\extension;


use Closure;
use Illuminate\Foundation\Http\FormRequest;
use rjapi\helpers\Config;

class BaseJwt extends FormRequest
{
    public function handle($request, Closure $next)
    {
        if (Config::getConfigKey())    
        return $next($request);
    }
}
<?php

namespace SoliDry\Extension;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BaseFormRequest
 * @package SoliDry\Extension
 */
class BaseFormRequest extends FormRequest
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * @return array
     */
    public function rules() : array
    {
        return [];
    }

    /**
     * @return array
     */
    public function relations() : array
    {
        return [];
    }
}
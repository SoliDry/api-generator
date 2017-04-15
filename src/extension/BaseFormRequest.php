<?php
/**
 * Created by Arthur Kushman
 * User: arthur
 * Date: 14.12.16
 * Time: 22:21
 */

namespace rjapi\extension;


use Closure;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
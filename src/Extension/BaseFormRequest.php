<?php
/**
 * Created by Arthur Kushman
 * User: arthur
 * Date: 14.12.16
 * Time: 22:21
 */

namespace SoliDry\Extension;


use Closure;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function rules(): array
    {
        return [];
    }

    public function relations(): array
    {
        return [];
    }
}
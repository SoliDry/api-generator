<?php
namespace Modules\V1\Http\Middleware;

use rjapi\extension\BaseFormRequest;

class ArticleMiddleware extends BaseFormRequest 
{
    public $id = null;
    // Attributes
    public $title = null;
    public $description = null;
    public $url = null;
    public $show_in_top = null;
    public $status = null;

    public  function authorize(): bool {
        return true;
    }

    public  function rules(): array {
        return [
            "title" => "required|string|min:16|max:256",
            "description" => "required|string|min:32|max:1024",
            "url" => "string|min:16|max:255",
            // Show at the top of main page
            "show_in_top" => "boolean",
            // The state of an article
            "status" => "in:draft,published,postponed,archived",
        ];
    }

    public  function relations(): array {
        return [
            "tags",
        ];
    }


}

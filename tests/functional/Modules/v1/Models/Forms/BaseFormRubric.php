<?php
namespace App\Modules\v1\Models\Forms;

use rjapi\extension\BaseFormRequest;

class BaseFormRubric extends BaseFormRequest 
{
    public $id = null;
    // Attributes
    public $name_rubric = null;
    public $url = null;
    public $meta_title = null;
    public $meta_description = null;
    public $show_menu = null;
    public $publish_rss = null;
    public $post_aggregator = null;
    public $display_tape = null;
    public $status = null;

    public  function authorize(): bool {
        return false;
    }

    public  function rules(): array {
        return [
            "name_rubric" => "required|string|min:8|max:500",
            "url" => "required|string|min:16|max:255",
            "meta_title" => "string|max:255",
            "meta_description" => "string|max:255",
            "show_menu" => "required|boolean",
            "publish_rss" => "required|boolean",
            "post_aggregator" => "required|boolean",
            "display_tape" => "required|boolean",
            "status" => "in:draft,published,postponed,archived",
        ];
    }

    public  function relations(): array {
        return [
            "tags",
        ];
    }


}

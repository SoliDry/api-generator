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
            [["name_rubric", "url", "show_menu", "publish_rss", "post_aggregator", "display_tape"], "required"], 
            ["id" , "integer"], 
            ["name_rubric" , "string"], 
            ["url" , "string"], 
            ["meta_title" , "string"], 
            ["meta_description" , "string"], 
            ["show_menu" , "boolean"], 
            ["publish_rss" , "boolean"], 
            ["post_aggregator" , "boolean"], 
            ["display_tape" , "boolean"], 
            ["status" , "in", "range" => ["draft", "published", "postponed", "archived"]]
        ];
    }

    public  function relations(): array {
        return [
            "tags",
        ];
    }


}

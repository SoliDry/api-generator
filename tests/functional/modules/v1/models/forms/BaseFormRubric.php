<?php
namespace app\modules\v1\models\forms;

use tass\extension\json\api\forms\BaseFormResource;

class BaseFormRubric extends BaseFormResource 
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

    public function rules(): array {
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
            ["display_tape" , "boolean"]
        ];
    }

    public function relations(): array {
        return [
            "tags",
        ];
    }
}

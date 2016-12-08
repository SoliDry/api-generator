<?php
namespace app\modules\v1\models\forms;

use rjapi\extension\json\api\forms\BaseFormResource;

class BaseFormTag extends BaseFormResource 
{
    public $id = null;
    // Attributes
    public $title = null;

    // Relations

    public function rules(): array {
        return [
            [["title"], "required"], 
            ["id" , "integer"], 
            ["title" , "string", "min" => "3", "max" => "255"]
        ];
    }

    public function relations(): array {
        return [
            "rubric",
        ];
    }
}

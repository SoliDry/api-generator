<?php
namespace App\Modules\v1\Models\Forms;

use rjapi\extension\BaseFormRequest;

class BaseFormTag extends BaseFormRequest 
{
    public $id = null;
    // Attributes
    public $title = null;

    // Relations

    public  function rules(): array {
        return [
            [["title"], "required"], 
            ["id" , "integer"], 
            ["title" , "string", "min" => "3", "max" => "255"]
        ];
    }

    public  function relations(): array {
        return [
            "rubric",
        ];
    }
}

<?php
namespace App\Modules\v1\Models\Mappers;

use rjapi\extension\BaseModel;

class BaseMapperRubric extends BaseModel 
{
    protected $primaryKey = "id";
    protected $table = "rubric";
    public $timestamps = false;

}

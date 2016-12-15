<?php
namespace App\Modules\v1\Models\Mappers;

use rjapi\extension\BaseModel;

class BaseMapperTag extends BaseModel 
{
    protected $primaryKey = "id";
    protected $table = "tag";
    public $timestamps = false;

}

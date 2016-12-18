<?php
namespace Modules\V1\Entities;

use rjapi\extension\BaseModel;

class Article extends BaseModel 
{
    protected $primaryKey = "id";
    protected $table = "article";
    public $timestamps = false;

}

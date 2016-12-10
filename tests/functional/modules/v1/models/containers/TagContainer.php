<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

use rjapi\extension\json\api\db\DataObjectTrait;

class TagContainer extends ActiveRecord 
{
    use DataObjectTrait;

    public static function tableName(): string {
        return "tag";
    }

    public  function rules(): string {
        return [];
    }

    public  function containers(): string {
        return [];
    }
}

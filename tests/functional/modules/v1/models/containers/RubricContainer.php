<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

use rjapi\extension\json\api\db\DataObjectTrait;

class RubricContainer extends ActiveRecord 
{
    use DataObjectTrait;

    public static function tableName(): string {
        return "rubric";
    }

    public  function rules(): string {
        return [];
    }

    public  function containers(): string {
        return [];
    }
}

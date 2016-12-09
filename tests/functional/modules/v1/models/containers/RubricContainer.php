<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

use app\modules\v1\models\mappers\DataObjectTrait;

class RubricContainer extends ActiveRecord 
{
    use app\modules\v1\models\mappers\DataObjectTrait;

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

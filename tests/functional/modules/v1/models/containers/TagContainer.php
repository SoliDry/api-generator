<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

class TagContainer extends ActiveRecord 
{
    use app\modules\v1\models\mappers\DataObjectTrait;

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

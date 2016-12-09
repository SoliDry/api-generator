<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

class RubricContainer extends ActiveRecord 
{
    public static function ActiveRecord(): string {
        return "rubric";
    }
}

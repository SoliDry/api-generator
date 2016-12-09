<?php
namespace app\modules\v1\containers;

use yii\db\ActiveRecord;

class RubricContainer extends ActiveRecord 
{
    public function ActiveRecord(): string {
        return "Rubric";
    }
}

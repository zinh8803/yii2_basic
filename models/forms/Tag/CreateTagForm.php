<?php

namespace app\models\forms\Tag;

use yii\base\Model;

class CreateTagForm extends Model
{
    public $name;
    public $slug;
    public $type;
    public $decription;

    public function rules()
    {
        return [
            [['name', 'slug', 'type', 'decription'], 'string', 'max' => 255],
        ];
    }
}

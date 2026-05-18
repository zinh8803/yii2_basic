<?php

namespace app\models\forms\Tag;

use yii\base\Model;

class CreateTagForm extends Model
{
    public $name;
    public $slug;
    public $type;
    public $description;

    public function rules()
    {
        return [
            [['name', 'slug', 'type', 'description'], 'string', 'max' => 255],
        ];
    }
}

<?php

namespace app\models\forms\Tag;

use yii\base\Model;

class UpdateTagForm extends Model
{
    public $id;
    public $name;
    public $slug;
    public $type;
    public $description;
    public $decription;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'slug', 'type', 'description', 'decription'], 'string', 'max' => 255],
        ];
    }
}

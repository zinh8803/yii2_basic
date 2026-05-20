<?php

namespace app\models\forms\Tag;

use app\models\Tags;
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
            [['name', 'type', 'description'], 'required'],
            [['name', 'slug', 'type', 'description'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => Tags::class, 'targetAttribute' => 'name'],
            [['slug'], 'unique', 'targetClass' => Tags::class, 'targetAttribute' => 'slug', 'skipOnEmpty' => true],
        ];
    }
}

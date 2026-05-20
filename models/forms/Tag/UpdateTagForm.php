<?php

namespace app\models\forms\Tag;

use app\models\Tags;
use yii\base\Model;

class UpdateTagForm extends Model
{
    public $id;
    public $name;
    public $slug;
    public $type;
    public $description;
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'slug', 'type', 'description'], 'string', 'max' => 255],
            [['name'], 'unique', 'targetClass' => Tags::class, 'targetAttribute' => 'name', 'filter' => ['<>', 'id', $this->id]],
            [['slug'], 'unique', 'targetClass' => Tags::class, 'targetAttribute' => 'slug', 'skipOnEmpty' => true, 'filter' => ['<>', 'id', $this->id]],
        ];
    }
}

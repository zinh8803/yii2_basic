<?php

namespace app\models\forms\Category;

use app\models\Categories;
use yii\base\Model;

class CreateCategoryForm extends Model
{
    public $name;
    public $slug;
    public $status;
    public $parent_id;
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'unique', 'targetClass' => Categories::class, 'targetAttribute' => 'name',],
            [['name'], 'required'],
            [['slug'], 'unique', 'targetClass' => Categories::class, 'targetAttribute' => 'slug'],
            [['parent_id', 'status'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::class, 'targetAttribute' => ['parent_id' => 'id']],
        ]);
    }
}

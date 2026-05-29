<?php
namespace app\models\forms\Brand;

use app\models\Brand;

class CreateBrandForm extends Brand
{
    public $name;
    public $slug;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['name'], 'unique', 'targetClass' => Brand::class, 'targetAttribute' => 'name',],
            [['slug'], 'unique', 'targetClass' => Brand::class, 'targetAttribute' => 'slug'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['status'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
        ];
    }
}

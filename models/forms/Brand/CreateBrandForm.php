<?php
namespace app\models\forms\Brand;

use app\models\Brands;
use yii\base\Model;

class CreateBrandForm extends Model
{
    public $name;
    public $slug;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['name'], 'unique', 'targetClass' => Brands::class, 'targetAttribute' => 'name',],
            [['slug'], 'unique', 'targetClass' => Brands::class, 'targetAttribute' => 'slug'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['status'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
        ];
    }
}

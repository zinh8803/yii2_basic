<?php

namespace app\models\forms\Product;

use app\models\Brands;
use app\models\Categories;
use app\models\Products;
use yii\base\Model;
use yii\web\UploadedFile;
class UpdateProductForm extends Model
{
    public $id;
    public $name;
    public $slug;
    public $description;
    public $status;
    public $category_id;
    public $brand_id;
    /** @var UploadedFile|null */
    public $imageFile;

    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [
                ['name'],
                'unique',
                'targetClass' => Products::class,
                'targetAttribute' => 'name',
                'filter' => function ($query) {
                    if ($this->id !== null) {
                        $query->andWhere(['<>', 'id', $this->id]);
                    }
                }
            ],
            [['name'], 'required'],
            [
                ['slug'],
                'unique',
                'targetClass' => Products::class,
                'targetAttribute' => 'slug',
                'filter' => function ($query) {
                    if ($this->id !== null) {
                        $query->andWhere(['<>', 'id', $this->id]);
                    }
                }
            ],
            [['category_id', 'brand_id', 'status'], 'integer'],
            [['name', 'slug', 'description'], 'string', 'max' => 255],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp', 'maxSize' => 5 * 1024 * 1024],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brands::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }
}

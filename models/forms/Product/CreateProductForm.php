<?php

namespace app\models\forms\Product;

use app\models\Brands;
use app\models\Categories;
use app\models\Files;
use app\models\Products;
use app\models\Resources;
use yii\base\Model;
use yii\web\UploadedFile;

class CreateProductForm extends Model
{
    public $name;
    public $slug;
    public $description;
    public $status;
    public $category_id;
    public $brand_id;
    /** @var UploadedFile|null */
    public $imageFile;
    public $image_file_id;
    public $image_resource_id;

    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['image_file_id', 'image_resource_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['name'], 'unique', 'targetClass' => Products::class, 'targetAttribute' => 'name',],
            [['name'], 'required'],
            [['slug'], 'unique', 'targetClass' => Products::class, 'targetAttribute' => 'slug'],
            [['category_id', 'brand_id', 'status', 'image_file_id', 'image_resource_id'], 'integer'],
            [['name', 'slug', 'description'], 'string', 'max' => 255],
            [['imageFile'], 'validateSingleImageSource'],
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 5 * 1024 * 1024,
            ],
            [
                ['image_file_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Files::class,
                'targetAttribute' => ['image_file_id' => 'id'],
            ],
            [
                ['image_resource_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Resources::class,
                'targetAttribute' => ['image_resource_id' => 'id'],
                'filter' => ['type' => 'image'],
            ],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brands::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    public function validateSingleImageSource(): void
    {
        $sources = array_filter([
            $this->imageFile instanceof UploadedFile,
            !empty($this->image_file_id),
            !empty($this->image_resource_id),
        ]);
        if (count($sources) > 1) {
            $this->addError('imageFile', 'Choose only one image source.');
        }
    }
}

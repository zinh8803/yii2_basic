<?php

namespace app\models\forms\Product;

use app\models\File;
use app\models\Product;
use app\models\Resource;
use yii\web\UploadedFile;

class ProductForm extends Product
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    /** @var UploadedFile|null */
    public $imageFile;
    public $image_file_id;
    public $image_resource_id;

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name',  'description', 'status', 'category_id', 'brand_id', 'imageFile', 'image_file_id', 'image_resource_id'],
            self::SCENARIO_UPDATE => ['name',  'description', 'status', 'category_id', 'brand_id', 'imageFile', 'image_file_id', 'image_resource_id'],
        ];
    }
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['image_file_id', 'image_resource_id'], 'default', 'value' => null],
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
                'targetClass' => File::class,
                'targetAttribute' => ['image_file_id' => 'id'],
            ],
            [
                ['image_resource_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Resource::class,
                'targetAttribute' => ['image_resource_id' => 'id'],
                'filter' => ['type' => 'image'],
            ],
        ]);
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

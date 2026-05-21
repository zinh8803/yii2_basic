<?php

namespace app\models\forms\ProductVariant;

use app\models\Files;
use app\models\Products;
use app\models\Resources;
use yii\base\Model;
use yii\web\UploadedFile;

class UpdateProductVariantForm extends Model
{
    public $id;
    public $product_id;
    public $name;
    public $sku;
    public $price;
    public $sale_price;
    public $cost_price;
    public $stock;
    public $weight;
    public $is_active;
    public $replace_images;
    /** @var UploadedFile[] */
    public $imageFiles = [];
    public $image_file_ids = [];
    public $image_resource_ids = [];

    public function beforeValidate(): bool
    {
        $this->image_file_ids = $this->normalizeIdArray($this->image_file_ids);
        $this->image_resource_ids = $this->normalizeIdArray($this->image_resource_ids);

        return parent::beforeValidate();
    }

    public function rules()
    {
        return [
            [['sku', 'sale_price', 'cost_price', 'weight'], 'default', 'value' => null],
            [['replace_images'], 'default', 'value' => false],
            [['id'], 'required'],
            [['id', 'product_id', 'stock'], 'integer'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
            [['is_active', 'replace_images'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['sku'], 'string', 'max' => 100],
            [
                ['imageFiles'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'checkExtensionByMimeType' => false,
                'maxSize' => 5 * 1024 * 1024,
                'maxFiles' => 10,
            ],
            [['image_file_ids'], 'each', 'rule' => ['integer']],
            [['image_resource_ids'], 'each', 'rule' => ['integer']],
            [['image_file_ids'], 'validateFileIds'],
            [['image_resource_ids'], 'validateImageResourceIds'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function validateFileIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, Files::class, 'Invalid file IDs: ');
    }

    public function validateImageResourceIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, Resources::class, 'Invalid image resource IDs: ', ['type' => 'image']);
    }

    private function validateExistingIds(string $attribute, string $modelClass, string $messagePrefix, array $filter = []): void
    {
        $ids = $this->{$attribute};
        if (empty($ids)) {
            return;
        }

        $query = $modelClass::find()
            ->select('id')
            ->where(['id' => $ids]);

        if ($filter !== []) {
            $query->andWhere($filter);
        }

        $existingIds = $query->column();
        $missingIds = array_values(array_diff($ids, array_map('intval', $existingIds)));
        if ($missingIds !== []) {
            $this->addError($attribute, $messagePrefix . implode(', ', $missingIds));
        }
    }

    private function normalizeIdArray($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);

        $ids = [];
        foreach ($items as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }
}

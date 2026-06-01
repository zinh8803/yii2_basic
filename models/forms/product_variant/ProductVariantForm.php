<?php

namespace app\models\forms\product_variant;

use app\models\File;
use app\models\ProductVariant;
use app\models\Resource;
use yii\web\UploadedFile;

class ProductVariantForm extends ProductVariant
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /** @var UploadedFile[] */
    public $imageFiles = [];
    public $image_file_ids = [];
    public $image_resource_ids = [];
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['product_id', 'name', 'sku', 'price', 'sale_price', 'cost_price', 'stock', 'weight', 'is_active', 'imageFiles', 'image_file_ids', 'image_resource_ids'];
        $scenarios[self::SCENARIO_UPDATE] = ['product_id', 'name', 'sku', 'price', 'sale_price', 'cost_price', 'stock', 'weight', 'is_active', 'imageFiles', 'image_file_ids', 'image_resource_ids'];
        return $scenarios;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
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
        ]);
    }

    public function beforeValidate()
    {
        $this->image_file_ids = $this->normalizeIdArray($this->image_file_ids);
        $this->image_resource_ids = $this->normalizeIdArray($this->image_resource_ids);

        return parent::beforeValidate();
    }

    public function validateFileIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, File::class, 'Invalid file IDs: ');
    }

    public function validateImageResourceIds(string $attribute): void
    {
        $this->validateExistingIds($attribute, Resource::class, 'Invalid image resource IDs: ', ['type' => 'image']);
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

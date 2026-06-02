<?php
namespace app\models;

use app\models\base\BaseProductVariant;
use app\behaviors\Timestamp;
use yii\helpers\Inflector;
use Yii;
use app\models\Resource;
use app\components\ResourceImageHelper;
class ProductVariant extends BaseProductVariant
{
    public const RESOURCE_TYPE = 'product_variant';
    public static function find(): query\ProductVariantQuery
    {
        return new query\ProductVariantQuery(get_called_class());
    }
    public function fields()
    {
        return [
            'id',
            'product_id',
            'name',
            'sku',
            'price',
            'sale_price',
            'cost_price',
            'stock',
            'weight',
            'is_active',
            'image' => function () {
                $resource = $this->primaryResource;
                return $resource ? $resource->file->url : null;
            },
            'images' => function () {
                return array_map(
                    function ($resource) {
                        return [
                            'id' => $resource->id,
                            'file_id' => $resource->file_id,
                            'url' => $resource->file->url,
                            'title' => $resource->title,
                            'alt_text' => $resource->alt_text,
                            'sort_order' => $resource->sort_order,
                            'is_primary' => $resource->is_primary,
                        ];
                    },
                    $this->resources
                );
            },
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
        ];
    }
    public function behaviors()
    {
        return [
            Timestamp::class,
            'sku' => [
                'class' => 'yii\behaviors\AttributeBehavior',
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'sku',
                    self::EVENT_BEFORE_UPDATE => 'sku',
                ],
                'value' => function () {
                    if ($this->isNewRecord) {
                        return $this->sku ?: $this->generateUniqueSku();
                    }

                    if ($this->isAttributeChanged('sku') && !empty($this->sku)) {
                        return $this->sku;
                    }

                    return $this->generateUniqueSku();
                },
            ],
        ];
    }
    public function getResources()
    {
        return $this->hasMany(Resource::class, ['resource_id' => 'id'])
            ->andWhere(['resource_type' => 'product_variant'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getPrimaryResource()
    {
        return $this->hasOne(Resource::class, ['resource_id' => 'id'])
            ->andWhere([
                'resource_type' => 'product_variant',
                'type' => 'image',
                'is_primary' => 1,
            ]);
    }

    private function generateUniqueSku(): string
    {
        $product = !empty($this->product_id) ? Product::findOne($this->product_id) : null;
        $productName = $product ? $product->name : null;

        $base = trim(($productName ?: '') . ' ' . ($this->name ?: ''));
        $skuBase = strtoupper(Inflector::slug($base, '-'));
        if ($skuBase === '') {
            $skuBase = 'SKU';
        }

        $sku = $skuBase;
        $suffix = 1;
        while (static::find()->where(['sku' => $sku])->exists()) {
            $sku = $skuBase . '-' . $suffix;
            $suffix++;
        }

        return $sku;
    }

    public function attachImagesFromForm($form): array
    {
        $hasPrimary = $this->getPrimaryResource()->exists();
        $currentSortOrder = $this->getResources()->max('sort_order');
        $sortOrder = $currentSortOrder === null ? 0 : (int) $currentSortOrder + 1;

        return ResourceImageHelper::attachImagesFromForm(
            self::RESOURCE_TYPE,
            $this->id,
            Yii::$app->user->id ?? 9,
            'uploads/product-variants',
            $form,
            $hasPrimary,
            $sortOrder
        );
    }
}

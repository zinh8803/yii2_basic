<?php
namespace app\models;

use app\behaviors\Timestamp;

class ProductVariant extends base\ProductVariant
{
    public static function find(): query\ProductVariantQuery
    {
        return new query\ProductVariantQuery(get_called_class());
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
}

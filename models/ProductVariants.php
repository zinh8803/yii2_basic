<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;

/**
 * This is the model class for table "product_variants".
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string|null $sku
 * @property float $price
 * @property float|null $sale_price
 * @property float|null $cost_price
 * @property int $stock
 * @property float|null $weight
 * @property boolean $is_active
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CartItems[] $cartItems
 * @property OrderItems[] $orderItems
 * @property Products $product
 */
class ProductVariants extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_variants';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sku', 'sale_price', 'cost_price', 'weight'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => 1],
            [['product_id', 'name', 'price'], 'required'],
            [['product_id', 'stock'], 'integer'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['sku'], 'string', 'max' => 100],
            [['is_active'], 'boolean'],
            [['sku'], 'unique'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
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

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'name' => 'Name',
            'sku' => 'Sku',
            'price' => 'Price',
            'sale_price' => 'Sale Price',
            'cost_price' => 'Cost Price',
            'stock' => 'Stock',
            'weight' => 'Weight',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItems::class, ['product_variant_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['variant_id' => 'id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }

    private function generateUniqueSku(): string
    {
        $productName = $this->product ? $this->product->name : null;
        if ($productName === null && $this->product_id) {
            $product = Products::findOne($this->product_id);
            $productName = $product ? $product->name : null;
        }

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

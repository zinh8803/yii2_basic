<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_items".
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property string $product_name
 * @property string|null $variant_name
 * @property string|null $image_url
 * @property string|null $sku
 * @property int $quantity
 * @property float $price
 *
 * @property Orders $order
 * @property Products $product
 * @property ProductVariants $variant
 */
class OrderItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['variant_id', 'variant_name', 'image_url', 'sku'], 'default', 'value' => null],
            [['order_id', 'product_id', 'product_name', 'quantity', 'price'], 'required'],
            [['order_id', 'product_id', 'variant_id', 'quantity'], 'integer'],
            [['price'], 'number'],
            [['product_name', 'image_url'], 'string', 'max' => 255],
            [['variant_name', 'sku'], 'string', 'max' => 100],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
            [['variant_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductVariants::class, 'targetAttribute' => ['variant_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'variant_id' => 'Variant ID',
            'product_name' => 'Product Name',
            'variant_name' => 'Variant Name',
            'image_url' => 'Image Url',
            'sku' => 'Sku',
            'quantity' => 'Quantity',
            'price' => 'Price',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::class, ['id' => 'order_id']);
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

    /**
     * Gets query for [[Variant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVariant()
    {
        return $this->hasOne(ProductVariants::class, ['id' => 'variant_id']);
    }

}

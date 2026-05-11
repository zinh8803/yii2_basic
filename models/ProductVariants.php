<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

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
 * @property float|null $weight
 * @property string $status
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
            [['status'], 'default', 'value' => 'active'],
            [['product_id', 'name', 'price'], 'required'],
            [['product_id'], 'integer'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['sku'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
            [['sku'], 'unique'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
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
            'weight' => 'Weight',
            'status' => 'Status',
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

}

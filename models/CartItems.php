<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cart_items".
 *
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $quantity
 * @property float $price
 *
 * @property Carts $cart
 * @property Products $product
 * @property ProductVariants $productVariant
 */
class CartItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_variant_id'], 'default', 'value' => null],
            [['cart_id', 'product_id', 'quantity', 'price'], 'required'],
            [['cart_id', 'product_id', 'product_variant_id', 'quantity'], 'integer'],
            [['price'], 'number'],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Carts::class, 'targetAttribute' => ['cart_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_variant_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductVariants::class, 'targetAttribute' => ['product_variant_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cart_id' => 'Cart ID',
            'product_id' => 'Product ID',
            'product_variant_id' => 'Product Variant ID',
            'quantity' => 'Quantity',
            'price' => 'Price',
        ];
    }

    /**
     * Gets query for [[Cart]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Carts::class, ['id' => 'cart_id']);
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
     * Gets query for [[ProductVariant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariant()
    {
        return $this->hasOne(ProductVariants::class, ['id' => 'product_variant_id']);
    }

}

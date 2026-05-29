<?php

namespace app\models\base;

use Yii;
use yii\db\ActiveRecord;

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
 * @property Order $order
 * @property Product $product
 * @property ProductVariant $variant
 */
class OrderItem extends ActiveRecord
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
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['variant_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductVariant::class, 'targetAttribute' => ['variant_id' => 'id']],
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
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Variant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVariant()
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id']);
    }

}

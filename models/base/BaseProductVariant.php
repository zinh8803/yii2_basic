<?php

namespace app\models\base;

use app\models\CartItem;
use app\models\OrderItem;
use app\models\Product;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
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
 * @property CartItem[] $cartItems
 * @property OrderItem[] $orderItems
 * @property Product $product
 * @property Resource[] $resources
 * @property Resource|null $primaryResource
 */
class BaseProductVariant extends ActiveRecord
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
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
        return $this->hasMany(CartItem::class, ['product_variant_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['variant_id' => 'id']);
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

}

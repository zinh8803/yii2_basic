<?php

namespace app\models\base;

use app\models\Brand;
use app\models\CartItem;
use app\models\Category;
use app\models\OrderItem;
use app\models\PostProduct;
use app\models\ProductAttribute;
use app\models\ProductVariant;
use app\models\Review;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $name
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property string $slug
 * @property string|null $description
 * @property int $status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Brand $brand
 * @property CartItem[] $cartItems
 * @property Category $category
 * @property OrderItem[] $orderItems
 * @property PostProduct[] $postProducts
 * @property ProductAttribute[] $productAttributes
 * @property ProductVariant[] $productVariants
 * @property Resource[] $resources
 * @property Resource|null $primaryResource
 * @property Review[] $reviews
 */
class BaseProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'brand_id', 'description'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['name', 'slug'], 'unique'],
            [['category_id', 'brand_id', 'status'], 'integer'],
            [['name', 'slug', 'description'], 'string', 'max' => 255],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'category_id' => 'Category ID',
            'brand_id' => 'Brand ID',
            'slug' => 'Slug',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'category',
            'brand',
            'productVariants',
            'productAttributes',
            'resources',
            'slug',
            'description',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::class, ['id' => 'brand_id']);
    }

    /**
     * Gets resources (images) for this product.
     * @return \yii\db\ActiveQuery
     */

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItem::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[PostProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostProducts()
    {
        return $this->hasMany(PostProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductAttributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributes()
    {
        return $this->hasMany(ProductAttribute::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductVariants]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariants()
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::class, ['product_id' => 'id']);
    }
}

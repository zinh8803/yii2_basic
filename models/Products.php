<?php

namespace app\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use app\models\Resources;

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
 * @property Brands $brand
 * @property CartItems[] $cartItems
 * @property Categories $category
 * @property OrderItems[] $orderItems
 * @property PostProducts[] $postProducts
 * @property ProductAttributes[] $productAttributes
 * @property ProductVariants[] $productVariants
 * @property Reviews[] $reviews
 */
class Products extends \yii\db\ActiveRecord
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
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brands::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,

            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
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
        return $this->hasOne(Brands::class, ['id' => 'brand_id']);
    }

    /**
     * Gets resources (images) for this product.
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resources::class, ['resource_id' => 'id'])
            ->andWhere(['resource_type' => 'product']);
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItems::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Categories::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[PostProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostProducts()
    {
        return $this->hasMany(PostProducts::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductAttributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributes()
    {
        return $this->hasMany(ProductAttributes::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductVariants]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariants()
    {
        return $this->hasMany(ProductVariants::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Reviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Reviews::class, ['product_id' => 'id']);
    }

}

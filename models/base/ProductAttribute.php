<?php

namespace app\models\base;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_attributes".
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string $type
 * @property string|null $slug
 * @property int|null $is_variant
 * @property int $sort_order
 * @property int $created_at
 * @property int $updated_at
 *
 * @property AttributeValue[] $attributeValues
 * @property Product $product
 */
class ProductAttribute extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['slug'], 'default', 'value' => null],
            [['type'], 'default', 'value' => 'text'],
            [['sort_order'], 'default', 'value' => 0],
            [['product_id', 'name'], 'required'],
            [['product_id', 'is_variant', 'sort_order'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
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
            'type' => 'Type',
            'slug' => 'Slug',
            'is_variant' => 'Is Variant',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AttributeValues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAttributeValues()
    {
        return $this->hasMany(AttributeValue::class, ['attribute_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
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

<?php

namespace app\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "attribute_values".
 *
 * @property int $id
 * @property int $attribute_id
 * @property string $value
 * @property string $slug
 * @property string|null $color_hex
 * @property int $sort_order
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ProductAttributes $attribute0
 */
class AttributeValues extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'attribute_values';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['color_hex'], 'default', 'value' => null],
            [['sort_order'], 'default', 'value' => 0],
            [['attribute_id', 'value', 'slug', 'created_at', 'updated_at'], 'required'],
            [['attribute_id', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['value', 'slug'], 'string', 'max' => 255],
            [['color_hex'], 'string', 'max' => 10],
            [['attribute_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductAttributes::class, 'targetAttribute' => ['attribute_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,

            [
                'class' => SluggableBehavior::class,
                'attribute' => 'value',
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
            'attribute_id' => 'Attribute ID',
            'value' => 'Value',
            'slug' => 'Slug',
            'color_hex' => 'Color Hex',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Attribute0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAttribute0()
    {
        return $this->hasOne(ProductAttributes::class, ['id' => 'attribute_id']);
    }

}

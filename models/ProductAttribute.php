<?php
namespace app\models;

use app\models\base\BaseProductAttribute;
use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;

class ProductAttribute extends BaseProductAttribute
{
    public static function find()
    {
        return new query\ProductAttributeQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            Timestamp::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
            ],
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

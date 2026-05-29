<?php
namespace app\models;

use app\behaviors\Timestamp;
use yii\behaviors\SluggableBehavior;

class Product extends base\Product
{

    public static function find(): query\ProductQuery
    {
        return new query\ProductQuery(get_called_class());
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
    public function getResources()
    {
        return $this->hasMany(Resource::class, ['resource_id' => 'id'])
            ->andWhere(['resource_type' => 'product']);
    }

    public function getPrimaryResource()
    {
        return $this->hasOne(Resource::class, ['resource_id' => 'id'])
            ->andWhere([
                'resource_type' => 'product',
                'type' => 'image',
                'is_primary' => 1,
            ]);
    }

}

<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class ProductQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere([
            'status' => 1
        ]);
    }

    public function byBrand($brandId)
    {
        return $this->andWhere([
            'brand_id' => $brandId
        ]);
    }

    public function byCategory($categoryId)
    {
        return $this->andWhere([
            'category_id' => $categoryId
        ]);
    }
}

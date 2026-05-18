<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class ProductAttributeQuery extends ActiveQuery
{
    public function byProduct($productId)
    {
        return $this->andWhere(['product_id' => $productId]);
    }

    public function variants()
    {
        return $this->andWhere(['is_variant' => 1]);
    }
}

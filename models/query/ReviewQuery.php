<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class ReviewQuery extends ActiveQuery
{
    public function approved()
    {
        return $this->andWhere(['is_approved' => 1]);
    }

    public function byProduct($productId)
    {
        return $this->andWhere(['product_id' => $productId]);
    }
}

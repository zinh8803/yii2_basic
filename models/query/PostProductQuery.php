<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class PostProductQuery extends ActiveQuery
{
    public function byPost($postId)
    {
        return $this->andWhere(['post_id' => $postId]);
    }

    public function byProduct($productId)
    {
        return $this->andWhere(['product_id' => $productId]);
    }
}

<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class PostQuery extends ActiveQuery
{
    public function published()
    {
        return $this->andWhere(['status' => 'published']);
    }

    public function byAuthor($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }
}

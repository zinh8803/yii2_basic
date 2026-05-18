<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class OrderQuery extends ActiveQuery
{
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function byStatus($status)
    {
        return $this->andWhere(['status' => $status]);
    }
}

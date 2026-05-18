<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class UserAddressQuery extends ActiveQuery
{
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }
}

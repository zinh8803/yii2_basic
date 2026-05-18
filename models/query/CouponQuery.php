<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class CouponQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['is_active' => 1]);
    }

    public function byCode($code)
    {
        return $this->andWhere(['code' => $code]);
    }
}

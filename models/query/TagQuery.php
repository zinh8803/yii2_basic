<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class TagQuery extends ActiveQuery
{
    public function byType($type)
    {
        return $this->andWhere(['type' => $type]);
    }
}

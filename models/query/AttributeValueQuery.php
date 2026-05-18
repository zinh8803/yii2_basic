<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class AttributeValueQuery extends ActiveQuery
{
    public function byAttribute($attributeId)
    {
        return $this->andWhere(['attribute_id' => $attributeId]);
    }
}

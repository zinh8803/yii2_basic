<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class BrandQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }

    public function bySlug($slug)
    {
        return $this->andWhere(['slug' => $slug]);
    }
}

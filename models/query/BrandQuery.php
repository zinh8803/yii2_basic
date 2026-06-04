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

    public function deleted()
    {
        return $this->andWhere(['is_deleted' => true]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['is_deleted' => false]);
    }

    public function withDeleted()
    {
        return $this;
    }
}

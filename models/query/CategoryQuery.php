<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class CategoryQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }

    public function roots()
    {
        return $this->andWhere(['parent_id' => null]);
    }

    public function byParent($parentId)
    {
        return $this->andWhere(['parent_id' => $parentId]);
    }
    public function tree()
    {
        return $this->with([
            'children' => function ($query) {
                $query->with('children');
            },
            'parent',
        ]);
    }
}

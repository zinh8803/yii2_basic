<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class TaggableQuery extends ActiveQuery
{
    public function byPost($postId)
    {
        return $this->andWhere(['post_id' => $postId]);
    }

    public function byTag($tagId)
    {
        return $this->andWhere(['tag_id' => $tagId]);
    }
}

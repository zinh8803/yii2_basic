<?php

namespace app\models\search;

use app\models\Taggable;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class TaggableSearch extends Taggable
{
    public function rules()
    {
        return [
            [['id', 'tag_id', 'post_id', 'created_at', 'updated_at'], 'integer'],
            [['type'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = Taggable::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $params['per_page'] ?? 10,
            ],
        ]);

        $this->load($params, $formName);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'tag_id' => $this->tag_id,
            'post_id' => $this->post_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}

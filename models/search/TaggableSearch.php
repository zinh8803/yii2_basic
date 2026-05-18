<?php

namespace app\models\search;

use app\models\Taggables;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class TaggableSearch extends Taggables
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
        $query = Taggables::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

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

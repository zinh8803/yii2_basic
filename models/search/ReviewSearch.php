<?php

namespace app\models\search;

use app\models\Reviews;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ReviewSearch extends Reviews
{
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'product_id', 'user_id', 'rating', 'is_approved', 'created_at', 'updated_at'], 'integer'],
            [['comment', 'keyword'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = Reviews::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);
        if ($this->keyword) {
            $query->andWhere(['like', 'comment', $this->keyword]);
        }

        return $dataProvider;
    }
}

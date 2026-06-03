<?php

namespace app\models\search;

use app\models\response\ReviewResponse;
use app\models\Review;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ReviewSearch extends Review
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
        $query = Review::find();
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

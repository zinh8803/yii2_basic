<?php

namespace app\models\search;

use app\models\Order;
use app\models\response\OrderResponse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'user_id', 'is_discounted', 'created_at', 'updated_at'], 'integer'],
            [['total', 'shipping_fee', 'discount_amount'], 'number'],
            [['order_code', 'stacking_id', 'email', 'receiver_name', 'receiver_phone', 'payment_method', 'payment_status', 'status', 'keyword'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = Order::find()->with(['orderItems']);
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
            'user_id' => $this->user_id,
            'is_discounted' => $this->is_discounted,
            'total' => $this->total,
            'shipping_fee' => $this->shipping_fee,
            'discount_amount' => $this->discount_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'order_code', $this->order_code])
            ->andFilterWhere(['like', 'stacking_id', $this->stacking_id])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'receiver_name', $this->receiver_name])
            ->andFilterWhere(['like', 'receiver_phone', $this->receiver_phone]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'order_code', $this->keyword],
                ['like', 'stacking_id', $this->keyword],
                ['like', 'email', $this->keyword],
                ['like', 'receiver_name', $this->keyword],
                ['like', 'receiver_phone', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

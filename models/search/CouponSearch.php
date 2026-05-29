<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Coupon;
use app\models\response\CouponResponse;

/**
 * CouponSearch represents the model behind the search form of `app\models\Coupon`.
 */
class CouponSearch extends Coupon
{
    public $keyword;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'max_usage', 'used_count', 'starts_at', 'expires_at', 'is_active', 'created_at', 'updated_at'], 'integer'],
            [['code', 'type', 'keyword'], 'safe'],
            [['value', 'min_order_value', 'max_discount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = '')
    {
        $query = CouponResponse::find()->active();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $params['per_page'] ?? 10,
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'value' => $this->value,
            'min_order_value' => $this->min_order_value,
            'max_discount' => $this->max_discount,
            'max_usage' => $this->max_usage,
            'used_count' => $this->used_count,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'type', $this->type]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'code', $this->keyword],
                ['like', 'type', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

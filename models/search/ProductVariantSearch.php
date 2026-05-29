<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductVariant;
use app\models\response\ProductVariantResponse;

/**
 * ProductVariantSearch represents the model behind the search form of `app\models\ProductVariants`.
 */
class ProductVariantSearch extends ProductVariant
{
    public $keyword;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'stock', 'is_active', 'created_at', 'updated_at'], 'integer'],
            [['name', 'sku', 'keyword'], 'safe'],
            [['price', 'sale_price', 'cost_price', 'weight'], 'number'],
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
        $query = ProductVariantResponse::find();

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
            'product_id' => $this->product_id,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'cost_price' => $this->cost_price,
            'stock' => $this->stock,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'sku', $this->sku]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->keyword],
                ['like', 'sku', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

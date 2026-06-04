<?php

namespace app\models\search;

use app\models\ProductAttribute;
use app\models\response\ProductAttributeResponse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProductAttributeSearch extends ProductAttribute
{
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'product_id', 'is_variant', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['name', 'type', 'slug', 'keyword'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = ProductAttribute::find()
            ->with(['attributeValues']);
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
            'is_variant' => $this->is_variant,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->keyword],
                ['like', 'type', $this->keyword],
                ['like', 'slug', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

<?php

namespace app\models\search;

use app\models\AttributeValues;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class AttributeValueSearch extends AttributeValues
{
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'attribute_id', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['value', 'slug', 'color_hex', 'keyword'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = AttributeValues::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'attribute_id' => $this->attribute_id,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'color_hex', $this->color_hex]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'value', $this->keyword],
                ['like', 'slug', $this->keyword],
                ['like', 'color_hex', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

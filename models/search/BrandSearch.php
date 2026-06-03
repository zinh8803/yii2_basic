<?php

namespace app\models\search;

use app\models\Brand;
use app\models\response\BrandResponse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BrandSearch represents the model behind the search form of `app\models\Brand`.
 */
class BrandSearch extends Brand
{
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'slug', 'status', 'keyword'], 'safe'],
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
    public function search($params, $formName = '', $active = true)
    {
        $query = Brand::find();

        if ($active) {
            $query->andWhere(['status' => 1]);
        }

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->keyword],
                ['like', 'slug', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

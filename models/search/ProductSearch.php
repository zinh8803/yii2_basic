<?php

namespace app\models\search;

use app\models\Product;
use app\models\response\ProductResponse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductSearch represents the model behind the search form of `app\models\Products`.
 */
class ProductSearch extends Product
{
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'brand_id', 'status', 'rating_count', 'created_at', 'updated_at'], 'integer'],
            [['rating_avg'], 'number'],
            [['name', 'slug', 'description'], 'safe'],
            [['keyword'], 'safe'],
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
        $query = Product::find()->with(['primaryResource.file', 'productVariants']);

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
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'status' => $this->status,
            'rating_count' => $this->rating_count,
            'rating_avg' => $this->rating_avg,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'description', $this->description]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->keyword],
                ['like', 'slug', $this->keyword],
                ['like', 'description', $this->keyword],
            ]);
        }

        return $dataProvider;
    }

    public function searchByCategory($categoryId, array $params = [], $formName = ''): ActiveDataProvider
    {
        $params['category_id'] = (int) $categoryId;

        return $this->search($params, $formName);
    }
}

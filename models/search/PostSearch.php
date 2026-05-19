<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Posts;
use app\models\response\Post\PostResponse;

/**
 * PostSearch represents the model behind the search form of `app\models\Posts`.
 */
class PostSearch extends Posts
{
    public $keyword;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'published_at', 'created_at', 'updated_at'], 'integer'],
            [['title', 'slug', 'excerpt', 'content', 'status', 'post_style', 'meta_title', 'meta_description', 'keyword'], 'safe'],
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
        $query = PostResponse::find()->with([
            'taggables.tag',
            'primaryResource.file',
            'postProducts.product.primaryResource.file'
        ]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'user_id' => $this->user_id,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['post_style' => $this->post_style])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'excerpt', $this->excerpt])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'meta_title', $this->meta_title])
            ->andFilterWhere(['like', 'meta_description', $this->meta_description]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'title', $this->keyword],
                ['like', 'slug', $this->keyword],
                ['like', 'excerpt', $this->keyword],
                ['like', 'content', $this->keyword],
            ]);
        }

        return $dataProvider;
    }
}

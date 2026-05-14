<?php

namespace app\controllers;

use app\models\forms\Post\CreatePostForm;
use app\models\Posts;
use app\models\search\PostSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class PostController extends BaseController
{
    public $modelClass = 'app\\models\\Posts';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Posts retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'Post retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreatePostForm();
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $post = new Posts();
            $post->user_id = $form->user_id;
            $post->title = $form->title;
            $post->slug = $form->slug;
            $post->excerpt = $form->excerpt;
            $post->content = $form->content;
            $post->status = $form->status;
            $post->post_style = $form->post_style;
            $post->meta_title = $form->meta_title;
            $post->meta_description = $form->meta_description;
            $post->published_at = $form->published_at;

            if ($post->save()) {
                return $this->json(true, $post, 'Post created successfully', 201);
            }

            Yii::error('Failed to save post: ' . json_encode($post->errors));
            foreach ($post->getErrors() as $attribute => $messages) {
                foreach ($messages as $message) {
                    $form->addError($attribute, $message);
                }
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->load($this->request->bodyParams, '');

        if ($model->save()) {
            return $this->json(true, $model, 'Post updated successfully');
        }

        return $this->json(false, $model->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Post deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = Posts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

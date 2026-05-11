<?php

namespace app\controllers;

use app\models\forms\Product\CreateProductForm;
use app\models\Files;
use app\models\Products;
use app\models\Resources;
use app\models\search\ProductSearch;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * ProductController implements the CRUD actions for Products model.
 */
class ProductController extends Controller
{
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

    /**
     * Lists all Products models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Products model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Products model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $form = new CreateProductForm();

        if ($this->request->isPost) {
            if ($form->load($this->request->post()) && $form->validate()) {
                $form->imageFile = UploadedFile::getInstance($form, 'imageFile');
                $product = new Products();
                $product->name = $form->name;
                $product->description = $form->description;
                $product->status = $form->status;
                $product->category_id = $form->category_id;
                $product->brand_id = $form->brand_id;

                if ($product->save()) {
                    if ($form->imageFile !== null) {


                        $uploadDir = Yii::getAlias('@webroot/uploads/products');
                        FileHelper::createDirectory($uploadDir);
                        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $form->imageFile->extension;
                        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
                        if ($form->imageFile->saveAs($fullPath)) {
                            $url = Yii::getAlias('@web/uploads/products/' . $fileName);
                            $size = @getimagesize($fullPath);

                            $file = new Files();
                            $file->user_id = Yii::$app->user->id;
                            $file->disk = 'local';
                            $file->path = 'uploads/products/' . $fileName;
                            $file->url = $url;
                            $file->original_name = $form->imageFile->name;
                            $file->mime_type = $form->imageFile->type;
                            $file->size_bytes = $form->imageFile->size;
                            $file->width = $size ? $size[0] : null;
                            $file->height = $size ? $size[1] : null;
                            $file->created_at = time();
                            $file->updated_at = time();
                            if ($file->save()) {
                                $resource = new Resources();
                                $resource->file_id = $file->id;
                                $resource->resource_type = 'product';
                                $resource->resource_id = $product->id;
                                $resource->type = 'image';
                                $resource->title = $file->original_name;
                                $resource->alt_text = null;
                                $resource->sort_order = 0;
                                $resource->is_primary = 1;
                                $resource->created_at = time();
                                $resource->updated_at = time();
                                if (!$resource->save()) {
                                    $form->addError('imageFile', 'Failed to save image resource.');
                                }
                            } else {
                                $form->addError('imageFile', 'Failed to save file record.');
                            }
                        } else {
                            $form->addError('imageFile', 'Failed to upload image.');
                        }
                    }
                    return $this->redirect(['view', 'id' => $product->id]);
                }

                foreach ($product->getErrors() as $attribute => $messages) {
                    foreach ($messages as $message) {
                        $form->addError($attribute, $message);
                    }
                }
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Products model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Products model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Products model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Products the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Products::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

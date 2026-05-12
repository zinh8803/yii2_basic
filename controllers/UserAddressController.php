<?php

namespace app\controllers;

use app\models\forms\UserAddress\CreateUserAddressForm;
use app\models\forms\UserAddress\UpdateUserAddressForm;
use app\models\UserAddresses;
use app\models\search\UserAddressSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserAddressController implements the CRUD actions for UserAddresses model.
 */
class UserAddressController extends Controller
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
     * Lists all UserAddresses models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserAddressSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserAddresses model.
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
     * Creates a new UserAddresses model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $form = new CreateUserAddressForm();

        if ($this->request->isPost) {
            if ($form->load($this->request->post()) && $form->validate()) {
                $model = new UserAddresses();
                $model->user_id = $form->user_id;
                $model->city = $form->city;
                $model->ward = $form->ward;
                $model->detail_address = $form->detail_address;
                $model->phone_number = $form->phone_number;
                $model->name_address = $form->name_address;
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing UserAddresses model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new UpdateUserAddressForm();
        $form->user_id = $model->user_id;
        $form->city = $model->city;
        $form->ward = $model->ward;
        $form->detail_address = $model->detail_address;
        $form->phone_number = $model->phone_number;
        $form->name_address = $model->name_address;
        if ($this->request->isPost && $form->load($this->request->post()) && $form->validate()) {
            $model->user_id = $form->user_id;
            $model->city = $form->city;
            $model->ward = $form->ward;
            $model->detail_address = $form->detail_address;
            $model->phone_number = $form->phone_number;
            $model->name_address = $form->name_address;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing UserAddresses model.
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
     * Finds the UserAddresses model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return UserAddresses the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserAddresses::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

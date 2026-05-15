<?php

namespace app\controllers;

use app\models\forms\UserAddress\CreateUserAddressForm;
use app\models\forms\UserAddress\UpdateUserAddressForm;
use app\models\UserAddresses;
use app\models\search\UserAddressSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UserAddressController extends BaseController
{
    public $modelClass = 'app\\models\\UserAddresses';

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
        $searchModel = new UserAddressSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'User addresses retrieved successfully');
    }
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->json(true, $model, 'User address retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreateUserAddressForm();
        $form->load($this->request->bodyParams, '');

        if ($form->validate()) {
            $model = new UserAddresses();
            $model->user_id = $form->user_id;
            $model->city = $form->city;
            $model->ward = $form->ward;
            $model->detail_address = $form->detail_address;
            $model->phone_number = $form->phone_number;
            $model->name_address = $form->name_address;
            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'User address created successfully', 201);
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new UpdateUserAddressForm();
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->load($data, '');
        if ($form->validate()) {
            $model->user_id = $form->user_id;
            $model->city = $form->city;
            $model->ward = $form->ward;
            $model->detail_address = $form->detail_address;
            $model->phone_number = $form->phone_number;
            $model->name_address = $form->name_address;
            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'User address updated successfully');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                return $this->json(false, null, 'Internal server error', 500);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model->delete()) {
                return $this->json(true, null, 'User address deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete user address', 500);
    }

    protected function findModel($id)
    {
        if (($model = UserAddresses::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

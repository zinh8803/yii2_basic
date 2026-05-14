<?php

namespace app\controllers;

use app\models\forms\UserAddress\CreateUserAddressForm;
use app\models\forms\UserAddress\UpdateUserAddressForm;
use app\models\UserAddresses;
use app\models\search\UserAddressSearch;
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
            if ($model->save()) {
                return $this->json(true, $model, 'User address created successfully', 201);
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

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

        $form->load($this->request->bodyParams, '');
        if ($form->validate()) {
            $model->user_id = $form->user_id;
            $model->city = $form->city;
            $model->ward = $form->ward;
            $model->detail_address = $form->detail_address;
            $model->phone_number = $form->phone_number;
            $model->name_address = $form->name_address;
            if ($model->save()) {
                return $this->json(true, $model, 'User address updated successfully');
            }
        }

        return $this->json(false, $form->errors, 'Validation failed', 422);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'User address deleted successfully');
    }

    protected function findModel($id)
    {
        if (($model = UserAddresses::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

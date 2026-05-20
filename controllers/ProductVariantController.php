<?php

namespace app\controllers;

use app\models\forms\ProductVariant\UpdateProductVariantForm;
use app\models\ProductVariants;
use app\models\forms\ProductVariant\CreateProductVariantForm;
use app\models\response\ProductVariant\ProductVariantResponse;
use app\models\search\ProductVariantSearch;
use Yii;

class ProductVariantController extends BaseController
{
    public $modelClass = 'app\models\ProductVariants';

    public function actionIndex()
    {
        $searchModel = new ProductVariantSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Product variants retrieved successfully');
    }

    public function actionView($id)
    {
        $query = ProductVariantResponse::findOne($id);
        if (!$query) {
            return $this->json(false, null, 'Product variant not found', 404);
        }
        return $this->json(true, $query, 'Product variant retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreateProductVariantForm();
        $form->load($this->request->bodyParams, '');
        try {
            if ($form->validate()) {
                $model = new ProductVariants();
                $model->setAttributes($form->attributes, false);
                if ($model->save()) {
                    return $this->json(true, $model, 'Product variant created successfully', 201);
                }
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }
        return $this->json(false, $form->errors, 'Validation failed', 422);
    }
    public function actionUpdate($id)
    {
        $model = ProductVariants::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Product variant not found', 404);
        }
        $form = new UpdateProductVariantForm();
        $form->id = $id;
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }
        $form->load($data, '');

        if ($form->validate()) {
            $model->setAttributes($form->attributes, false);

            try {
                if ($model->save()) {
                    return $this->json(true, $model, 'Product variant updated successfully');
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
        $model = ProductVariants::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Product variant not found', 404);
        }
        try {
            if ($model->delete()) {
                return $this->json(true, null, 'Product variant deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete product variant', 500);
    }
}

<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\models\forms\product_variant\ProductVariantForm;
use app\models\ProductVariant;
use app\models\search\ProductVariantSearch;
use Yii;
use yii\web\NotFoundHttpException;

class ProductVariantController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new ProductVariantSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, 'Product variants retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Product variant retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new ProductVariantForm([
            'scenario' => ProductVariantForm::SCENARIO_CREATE,
        ]);
        $this->loadVariantForm($form);

        $attributesToValidate = array_diff($form->activeAttributes(), ['imageFiles']);
        if (!$form->validate($attributesToValidate)) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model = new ProductVariant();
            $this->applyFormToVariant($model, $form);
            if (!$model->save(false)) {
                throw new \RuntimeException('Failed to save product variant.');
            }
            $imageErrors = $model->attachImagesFromForm($form);

            $transaction->commit();

            $responseModel = $this->findModel($model->id);
            $imageErrors = $imageErrors ?? [];
            $data = [
                'variant' => $responseModel,
                'image_errors' => $imageErrors,
                'image_error_count' => count($imageErrors),
            ];
            $message = 'Product variant created successfully';
            if ($imageErrors !== []) {
                $message .= '. ' . count($imageErrors) . ' image(s) failed: '
                    . implode(', ', array_column($imageErrors, 'name'));
            }

            return $this->formatJson(true, $data, $message, self::HTTP_CREATED);
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, $exception->getMessage(), 'Internal server error', 500, );
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new ProductVariantForm([
            'scenario' => ProductVariantForm::SCENARIO_UPDATE,
        ]);
        $form->id = $id;
        $this->loadVariantForm($form);
        $attributesToValidate = array_diff($form->activeAttributes(), ['imageFiles']);
        if (!$form->validate($attributesToValidate)) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->applyFormToVariant($model, $form);
            if (!$model->save(false)) {
                $this->addModelErrors($form, $model);
                throw new \RuntimeException('Failed to save product variant.');
            }
            $imageErrors = $model->attachImagesFromForm($form);

            $transaction->commit();
            $responseModel = $this->findModel($model->id);
            $imageErrors = $imageErrors ?? [];
            $data = [
                'variant' => $responseModel,
                'image_errors' => $imageErrors,
                'image_error_count' => count($imageErrors),
            ];
            $message = 'Product variant updated successfully';
            if ($imageErrors !== []) {
                $message .= '. ' . count($imageErrors) . ' image(s) failed: '
                    . implode(', ', array_column($imageErrors, 'name'));
            }

            return $this->formatJson(true, $data, $message);
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, $form->errors ?: null, 'Internal server error', 500);
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            ResourceImageHelper::deleteImageResourceLinks('product_variant', $model->id);
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Product variant deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, null, 'Failed to delete product variant', 500);
    }
    public function findModel($id)
    {
        $model = ProductVariantForm::find()
            ->where(['id' => $id])
            ->with(['resources.file', 'primaryResource.file'])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Product variant not found');
        }
        return $model;
    }
    private function loadVariantForm(ProductVariantForm $form): void
    {
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        $data = $isMultipart ? $request->post() : $request->bodyParams;
        if (empty($data)) {
            $data = $request->post();
        }

        $form->load($data, '');
        if ($isMultipart) {
            $form->imageFiles = ResourceImageHelper::getUploadedImageFiles($form);
        }
    }

    private function applyFormToVariant(ProductVariant $model, ProductVariantForm $form): void
    {
        $model->setAttributes($form->getAttributes([
            'product_id',
            'name',
            'sku',
            'price',
            'sale_price',
            'cost_price',
            'stock',
            'weight',
            'is_active',
        ]), false);
    }
}

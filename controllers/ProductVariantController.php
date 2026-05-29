<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\models\forms\ProductVariant\CreateProductVariantForm;
use app\models\forms\ProductVariant\UpdateProductVariantForm;
use app\models\ProductVariants;
use app\models\response\ProductVariant\ProductVariantResponse;
use app\models\search\ProductVariantSearch;
use Yii;
use yii\base\Model;

class ProductVariantController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new ProductVariantSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Product variants retrieved successfully');
    }

    public function actionView($id)
    {
        $model = ProductVariantResponse::find()
            ->with(['resources.file', 'primaryResource.file'])
            ->where(['id' => $id])
            ->one();
        if (!$model) {
            return $this->json(false, null, 'Product variant not found', 404);
        }
        return $this->json(true, $model, 'Product variant retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new CreateProductVariantForm();
        $this->loadVariantForm($form);

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $model = new ProductVariants();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->applyFormToVariant($model, $form);
            if (!$model->save()) {
                $this->addModelErrors($form, $model);
                throw new \RuntimeException('Failed to save product variant.');
            }

            $this->attachVariantImagesFromForm($model, $form, true);

            $transaction->commit();
            $responseModel = ProductVariantResponse::find()
                ->with(['resources.file', 'primaryResource.file'])
                ->where(['id' => $model->id])
                ->one();

            return $this->json(true, $responseModel, 'Product variant created successfully', 201);
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, $form->errors ?: null, 'Internal server error', 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = ProductVariants::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Product variant not found', 404);
        }

        $form = new UpdateProductVariantForm();
        $form->id = $id;
        $this->loadVariantForm($form);

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->applyFormToVariant($model, $form);
            if (!$model->save()) {
                $this->addModelErrors($form, $model);
                throw new \RuntimeException('Failed to save product variant.');
            }

            $this->attachVariantImagesFromForm($model, $form, (bool) $form->replace_images);

            $transaction->commit();
            $responseModel = ProductVariantResponse::find()
                ->with(['resources.file', 'primaryResource.file'])
                ->where(['id' => $model->id])
                ->one();

            return $this->json(true, $responseModel, 'Product variant updated successfully');
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, $form->errors ?: null, 'Internal server error', 500);
        }
    }

    public function actionDelete($id)
    {
        $model = ProductVariants::findOne($id);
        if (!$model) {
            return $this->json(false, null, 'Product variant not found', 404);
        }
        try {
            ResourceImageHelper::deleteImageResourceLinks('product_variant', $model->id);
            if ($model->delete()) {
                return $this->json(true, null, 'Product variant deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete product variant', 500);
    }

    private function loadVariantForm(CreateProductVariantForm|UpdateProductVariantForm $form): void
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

    private function applyFormToVariant(ProductVariants $model, CreateProductVariantForm|UpdateProductVariantForm $form): void
    {
        foreach (['product_id', 'name', 'sku', 'price', 'sale_price', 'cost_price', 'stock', 'weight', 'is_active'] as $attribute) {
            if ($form->{$attribute} !== null) {
                $model->{$attribute} = $form->{$attribute};
            }
        }
    }

    private function attachVariantImagesFromForm(
        ProductVariants $variant,
        CreateProductVariantForm|UpdateProductVariantForm $form,
        bool $replaceImages = false
    ): void {
        $hasImages = !empty($form->imageFiles)
            || !empty($form->image_file_ids)
            || !empty($form->image_resource_ids);

        if (!$hasImages) {
            return;
        }

        if ($replaceImages) {
            ResourceImageHelper::deleteImageResourceLinks('product_variant', $variant->id);
        }

        $hasPrimary = !$replaceImages && $variant->getPrimaryResource()->exists();
        $sortOrder = (int) $variant->getResources()->max('sort_order') + 1;

        foreach ($form->imageFiles as $imageFile) {
            ResourceImageHelper::attachImage(
                'product_variant',
                $variant->id,
                9,
                'uploads/product-variants',
                $imageFile,
                $form,
                !$hasPrimary,
                $sortOrder++
            );
            $hasPrimary = true;
        }

        foreach ($form->image_file_ids as $fileId) {
            ResourceImageHelper::attachExistingImageFile(
                'product_variant',
                $variant->id,
                (int) $fileId,
                !$hasPrimary,
                $sortOrder++
            );
            $hasPrimary = true;
        }

        foreach ($form->image_resource_ids as $resourceId) {
            ResourceImageHelper::attachExistingImageResource(
                'product_variant',
                $variant->id,
                (int) $resourceId,
                !$hasPrimary,
                $sortOrder++
            );
            $hasPrimary = true;
        }
    }

    private function addModelErrors(Model $form, Model $model): void
    {
        foreach ($model->getErrors() as $attribute => $messages) {
            foreach ($messages as $message) {
                $form->addError($attribute, $message);
            }
        }
    }
}

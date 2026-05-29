<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\models\forms\Product\CreateProductForm;
use app\models\forms\Product\UpdateProductForm;
use app\models\Products;
use Yii;
use yii\base\Model;
use app\controllers\BaseController as BaseController;
use app\models\response\Product\ProductResponse;
use app\models\search\ProductSearch;
use yii\web\UploadedFile;

class ProductController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Get list product successfully');
        //return $this->successPaginate($dataProvider);
    }


    public function actionView($id)
    {
        $model = ProductResponse::find()
            ->with([
                'category',
                'brand',
                'productVariants.resources.file',
                'productVariants.primaryResource.file',
                'productAttributes.attributeValues',
                'primaryResource.file'
            ])
            ->where(['id' => $id])
            ->one();
        if (!$model) {
            return $this->json(false, null, 'Product not found', 404);
        }
        return $this->json(true, $model, 'Product retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreateProductForm();
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;

        if ($isMultipart) {
            $form->load($request->post(), '');
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
        } else {
            $form->load($request->bodyParams, '');
        }

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $product = new Products();

        try {
            if ($this->createProduct($product, $form)) {
                $responseModel = ProductResponse::find()->where(['id' => $product->id])->one();
                return $this->json(true, $responseModel, 'Product created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $form->errors, 'Failed to create product', 400);
    }

    public function actionByCategory($categoryId)
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->searchByCategory($categoryId, $this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Get list product by category successfully');
    }

    public function actionUpdate($id)
    {
        $product = $this->findModel($id);
        $form = $this->buildUpdateForm($product);
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        $data = [];

        if ($isMultipart) {
            $data = $request->post();
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
        } else {
            $data = $request->bodyParams;
        }

        $form->load($data, '');

        if ($isMultipart && empty($data) && $form->imageFile === null) {
            return $this->json(
                false,
                null,
                'PUT/PATCH multipart/form-data is not supported by PHP. Use POST with _method=PUT or send JSON body.',
                400
            );
        }

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        try {
            if ($this->updateProduct($product, $form)) {
                $responseModel = ProductResponse::find()->where(['id' => $product->id])->one();
                return $this->json(true, $responseModel, 'Product updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, $form->errors, 'Failed to update product', 400);
    }
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            if ($model->delete()) {
                return $this->json(true, null, 'Product deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->json(false, null, 'Internal server error', 500);
        }

        return $this->json(false, null, 'Failed to delete product', 500);
    }
    protected function findModel($id)
    {
        if (($model = Products::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
    }

    private function buildUpdateForm(Products $product): UpdateProductForm
    {
        $form = new UpdateProductForm();
        $form->id = $product->id;

        return $form;
    }

    private function createProduct(Products $product, CreateProductForm $form): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $product->setAttributes([
                'name' => $form->name,
                'slug' => $form->slug,
                'description' => $form->description,
                'status' => $form->status,
                'category_id' => $form->category_id,
                'brand_id' => $form->brand_id,
            ], false);

            if (!$product->save()) {
                $this->addModelErrors($form, $product);
                throw new \RuntimeException('Failed to save product.');
            }

            $this->attachProductImageFromForm($product, $form);

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    private function updateProduct(Products $product, UpdateProductForm $form): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $product->setAttributes([
                'name' => $form->name,
                'slug' => $form->slug,
                'description' => $form->description,
                'status' => $form->status,
                'category_id' => $form->category_id,
                'brand_id' => $form->brand_id,
            ], false);

            if (!$product->save()) {
                $this->addModelErrors($form, $product);
                throw new \RuntimeException('Failed to save product.');
            }

            $this->attachProductImageFromForm($product, $form, true);

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    private function attachProductImageFromForm(
        Products $product,
        CreateProductForm|UpdateProductForm $form,
        bool $replacePrimary = false
    ): void {
        if (
            !$form->imageFile instanceof UploadedFile
            && empty($form->image_file_id)
            && empty($form->image_resource_id)
        ) {
            return;
        }

        if ($replacePrimary) {
            ResourceImageHelper::markImagesNonPrimary('product', $product->id);
        }

        if ($form->imageFile instanceof UploadedFile) {
            ResourceImageHelper::attachImage('product', $product->id, 9, 'uploads/products', $form->imageFile, $form);
            return;
        }

        if (!empty($form->image_resource_id)) {
            ResourceImageHelper::attachExistingImageResource('product', $product->id, (int) $form->image_resource_id);
            return;
        }

        ResourceImageHelper::attachExistingImageFile('product', $product->id, (int) $form->image_file_id);
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

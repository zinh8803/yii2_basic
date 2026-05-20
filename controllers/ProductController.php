<?php

namespace app\controllers;

use app\models\forms\Product\CreateProductForm;
use app\models\forms\Product\UpdateProductForm;
use app\models\Files;
use app\models\Products;
use app\models\Resources;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use app\controllers\BaseController as BaseController;
use app\models\response\Product\ProductResponse;
use app\models\search\ProductSearch;
use yii\web\UploadedFile;

class ProductController extends BaseController
{
    public $modelClass = 'app\models\Products';
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $data = $this->paginate($dataProvider->query);
        return $this->json(true, $data, 'Get list product successfully');
    }


    public function actionView($id)
    {
        $model = ProductResponse::find()
            ->with([
                'category',
                'productVariants',
                'productAttributes',
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
            $form->imageFile = UploadedFile::getInstanceByName('imageFile');
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
            $form->imageFile = UploadedFile::getInstanceByName('imageFile');
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

            if ($form->imageFile instanceof UploadedFile) {
                $this->attachImage($product, $form->imageFile, $form, true);
            }

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

            if ($form->imageFile instanceof UploadedFile) {
                $this->markProductImagesNonPrimary($product);
                $this->attachImage($product, $form->imageFile, $form, true);
            }

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

    private function attachImage(Products $product, UploadedFile $imageFile, Model $form, bool $isPrimary = true): void
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/products');
        FileHelper::createDirectory($uploadDir);

        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $imageFile->extension;
        $relativePath = 'uploads/products/' . $fileName;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$imageFile->saveAs($fullPath)) {
            $form->addError('imageFile', 'Failed to upload image.');
            throw new \RuntimeException('Failed to upload image.');
        }

        try {
            $file = $this->createFileRecord($imageFile, $relativePath, $fullPath);
            $this->createImageResource($product, $file, $isPrimary);
        } catch (\Throwable $e) {
            @unlink($fullPath);
            if (!$form->hasErrors('imageFile')) {
                $form->addError('imageFile', 'Failed to save image information.');
            }
            throw $e;
        }
    }

    private function createFileRecord(UploadedFile $imageFile, string $relativePath, string $fullPath): Files
    {
        $size = @getimagesize($fullPath);

        $file = new Files();
        $file->user_id = 9;
        $file->disk = 'local';
        $file->path = $relativePath;
        $file->url = Yii::getAlias('@web/' . $relativePath);
        $file->original_name = $imageFile->name;
        $file->mime_type = $imageFile->type;
        $file->size_bytes = $imageFile->size;
        $file->width = $size ? $size[0] : null;
        $file->height = $size ? $size[1] : null;

        if (!$file->save()) {
            throw new \RuntimeException('Failed to save file record: ' . json_encode($file->errors));
        }

        return $file;
    }

    private function createImageResource(Products $product, Files $file, bool $isPrimary): void
    {
        $resource = new Resources();
        $resource->file_id = $file->id;
        $resource->resource_type = 'product';
        $resource->resource_id = $product->id;
        $resource->type = 'image';
        $resource->title = $file->original_name;
        $resource->alt_text = null;
        $resource->sort_order = 0;
        $resource->is_primary = $isPrimary ? 1 : 0;

        if (!$resource->save()) {
            throw new \RuntimeException('Failed to save image resource: ' . json_encode($resource->errors));
        }
    }

    private function markProductImagesNonPrimary(Products $product): void
    {
        Resources::updateAll(
            ['is_primary' => 0],
            [
                'resource_type' => 'product',
                'resource_id' => $product->id,
                'type' => 'image',
                'is_primary' => 1,
            ]
        );
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

<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\controllers\BaseController as BaseController;
use app\models\forms\Product\ProductForm;
use app\models\Product;
use app\models\search\ProductSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProductController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view'],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, "Products fetched successfully");
    }


    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Product retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('product.create');
        $form = new ProductForm([
            'scenario' => ProductForm::SCENARIO_CREATE,
        ]);

        $this->loadProductForm($form);

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $product = new Product();
        try {
            if ($this->saveProduct($product, $form)) {
                $responseModel = $this->findModel($product->id);
                return $this->formatJson(true, $responseModel, 'Product created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, $form->errors, 'Failed to create product', 400);
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('product.update');
        $product = $this->findModel($id);
        $form = new ProductForm([
            'scenario' => ProductForm::SCENARIO_UPDATE,
        ]);
        $form->id = $product->id;
        $this->loadProductForm($form);

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        try {
            if ($this->saveProduct($product, $form)) {
                $responseModel = $this->findModel($product->id);
                return $this->formatJson(true, $responseModel, 'Product updated successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, $form->errors, 'Failed to update product', 400);
    }

    public function actionDelete($id)
    {
        $this->checkPermission('product.delete');
        $model = $this->findModel($id);
        try {
            ResourceImageHelper::deleteImageResourceLinks('product', $model->id);
            if ($model->delete()) {
                return $this->formatJson(true, null, 'Product deleted successfully');
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Internal server error', 500);
        }

        return $this->formatJson(false, null, 'Failed to delete product', 500);
    }

    protected function findModel($id)
    {
        $model = ProductForm::find()
            ->where(['id' => $id])
            ->with([
                'category',
                'brand',
                'productVariants.resources.file',
                'productVariants.primaryResource.file',
                'productAttributes.attributeValues',
                'primaryResource.file'
            ])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('Product not found');
        }
        return $model;
    }

    public function loadProductForm(ProductForm $form): void
    {
        $request = Yii::$app->request;
        $isMultipart = strpos((string) $request->getContentType(), 'multipart/form-data') !== false;
        $data = $isMultipart ? $request->post() : $request->bodyParams;
        if (empty($data)) {
            $data = $request->post();
        }
        $form->load($data, '');
        if ($isMultipart) {
            $form->imageFile = ResourceImageHelper::getUploadedImageFile($form);
        }
    }

    private function saveProduct(Product $product, ProductForm $form): bool
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

            if (!$product->save(false)) {
                foreach ($product->getErrors() as $attribute => $messages) {
                    foreach ($messages as $message) {
                        Yii::error("Product save error on $attribute: $message", __METHOD__);
                    }
                }
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

    private function attachProductImageFromForm(Product $product, ProductForm $form): void
    {
        if (
            !$form->imageFile instanceof UploadedFile
            && empty($form->image_file_id)
            && empty($form->image_resource_id)
        ) {
            return;
        }

        ResourceImageHelper::markImagesNonPrimary('product', $product->id);

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


}

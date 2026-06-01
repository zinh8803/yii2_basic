<?php

namespace app\controllers;

use app\components\ResourceImageHelper;
use app\models\File;
use app\models\forms\product_variant\ProductVariantForm;
use app\models\ProductVariant;
use app\models\Resource;
use app\models\response\ProductVariantResponse;
use app\models\search\ProductVariantSearch;
use Yii;
use yii\base\Model;
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

        $attributesToValidate = array_diff($form->activeAttributes(), ['imageFiles']);
        if (!$form->validate($attributesToValidate)) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model = new ProductVariant();

            foreach ([
                'product_id',
                'name',
                'sku',
                'price',
                'sale_price',
                'cost_price',
                'stock',
                'weight',
                'is_active',
            ] as $attribute) {
                if ($form->{$attribute} !== null) {
                    $model->{$attribute} = $form->{$attribute};
                }
            }

            if (!$model->save(false)) {
                throw new \RuntimeException('Failed to save product variant.');
            }
            $hasImages = !empty($form->imageFiles)
                || !empty($form->image_file_ids)
                || !empty($form->image_resource_ids);
            if ($hasImages) {
                $sortOrder = 0;
                $resourceRows = [];
                $imageErrors = [];
                $uploadedFiles = [];
                $hasPrimary = false;
                $time = time();
                $imageFiles = $form->imageFiles;

                if (count($imageFiles) > 10) {
                    foreach (array_slice($imageFiles, 10) as $imageFile) {
                        $imageErrors[] = [
                            'name' => $imageFile->name,
                            'error' => 'Maximum image count exceeded.',
                        ];
                    }
                    $imageFiles = array_slice($imageFiles, 0, 10);
                }

                foreach ($imageFiles as $imageFile) {
                    try {
                        $file = ResourceImageHelper::createFileRecord(
                            9,
                            'uploads/product-variants',
                            $imageFile
                        );
                        $uploadedFiles[] = $file;
                    } catch (\Throwable $exception) {
                        $imageErrors[] = [
                            'name' => $imageFile->name,
                            'error' => $exception->getMessage(),
                        ];
                        Yii::warning($exception->getMessage(), __METHOD__);
                        continue;
                    }

                    $resourceRows[] = [
                        $file->id,
                        'product_variant',
                        $model->id,
                        'image',
                        $imageFile->name,
                        null,
                        $sortOrder++,
                        $hasPrimary ? 0 : 1,
                        $time,
                        $time,
                    ];

                    $hasPrimary = true;
                }

                $existingFiles = [];
                if (!empty($form->image_file_ids)) {
                    $existingFiles = File::find()
                        ->where(['id' => $form->image_file_ids])
                        ->indexBy('id')
                        ->all();
                }

                foreach ($form->image_file_ids as $fileId) {
                    $file = $existingFiles[(int) $fileId] ?? null;
                    if ($file === null) {
                        $imageErrors[] = [
                            'name' => (string) $fileId,
                            'error' => 'File not found.',
                        ];
                        continue;
                    }

                    $resourceRows[] = [
                        (int) $fileId,
                        'product_variant',
                        $model->id,
                        'image',
                        $file->original_name,
                        null,
                        $sortOrder++,
                        $hasPrimary ? 0 : 1,
                        $time,
                        $time,
                    ];

                    $hasPrimary = true;
                }

                $existingResources = [];
                if (!empty($form->image_resource_ids)) {
                    $existingResources = Resource::find()
                        ->where([
                            'id' => $form->image_resource_ids,
                            'type' => 'image',
                        ])
                        ->indexBy('id')
                        ->all();
                }

                foreach ($form->image_resource_ids as $resourceId) {
                    $resource = $existingResources[(int) $resourceId] ?? null;
                    if ($resource === null) {
                        $imageErrors[] = [
                            'name' => (string) $resourceId,
                            'error' => 'Image resource not found.',
                        ];
                        continue;
                    }

                    $resourceRows[] = [
                        (int) $resource->file_id,
                        'product_variant',
                        $model->id,
                        'image',
                        $resource->title,
                        $resource->alt_text,
                        $sortOrder++,
                        $hasPrimary ? 0 : 1,
                        $time,
                        $time,
                    ];

                    $hasPrimary = true;
                }

                if (!empty($resourceRows)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            Resource::tableName(),
                            [
                                'file_id',
                                'resource_type',
                                'resource_id',
                                'type',
                                'title',
                                'alt_text',
                                'sort_order',
                                'is_primary',
                                'created_at',
                                'updated_at',
                            ],
                            $resourceRows
                        )->execute();
                    } catch (\Throwable $exception) {
                        foreach ($uploadedFiles as $uploadedFile) {
                            $fullPath = Yii::getAlias('@webroot/' . ltrim($uploadedFile->path, '/\\'));
                            if (is_file($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                        throw $exception;
                    }
                }
            }

            $transaction->commit();

            $responseModel = ProductVariantResponse::find()
                ->with(['resources.file', 'primaryResource.file'])
                ->where(['id' => $model->id])
                ->one();
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

            return $this->formatJson(
                true,
                $data,
                $message,
                201
            );
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(
                false,
                $exception->getMessage(),
                'Internal server error',
                500,

            );
        }
    }

    public function actionUpdate($id)
    {
        $model = ProductVariant::findOne($id);
        if (!$model) {
            return $this->formatJson(false, null, 'Product variant not found', 404);
        }

        $form = new UpdateProductVariantForm();
        $form->id = $id;
        $this->loadVariantForm($form);

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
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

            return $this->formatJson(true, $responseModel, 'Product variant updated successfully');
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
        $model = ProductVariants::findOne($id);
        if (!$model) {
            return $this->formatJson(false, null, 'Product variant not found', 404);
        }
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
}

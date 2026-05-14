<?php

namespace app\controllers;

use app\models\AttributeValues;
use app\models\forms\ProductAttribute\CreateProductAttribute;
use app\models\forms\ProductAttribute\UpdateProductAttribute;
use app\models\ProductAttributes;
use app\models\response\ProductAttribute\ProductAttributeResponse;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ProductAttributeController extends BaseController
{
    public $modelClass = 'app\models\ProductAttributes';

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex()
    {
        $query = ProductAttributeResponse::find();
        $data = $this->paginate($query);
        return $this->json(true, $data, 'Product attributes retrieved successfully');
    }
    public function actionView($id)
    {
        $model = ProductAttributeResponse::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Product attribute not found');
        }
        return $this->json(true, $model, 'Product attribute retrieved successfully');
    }
    public function actionCreate()
    {
        $form = new CreateProductAttribute();
        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }

        $form->load($data, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new ProductAttributes();
            $model->product_id = $form->product_id;
            $model->name = $form->name;
            $model->type = $form->type;
            $model->slug = $form->slug;
            $model->is_variant = $form->is_variant;
            $model->sort_order = $form->sort_order;

            if (!$model->save()) {
                $transaction->rollBack();
                return $this->json(false, $model->errors, 'Validation failed', 422);
            }

            if (is_array($form->attribute_value)) {
                $this->saveAttributeValues($model->id, $form->attribute_value);
            }

            $transaction->commit();
            return $this->json(true, $model, 'Product attribute created successfully', 201);
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->json(false, null, $e->getMessage(), 500);
        }
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new UpdateProductAttribute();
        $form->id = $model->id;
        $form->product_id = $model->product_id;
        $form->name = $model->name;
        $form->type = $model->type;
        $form->slug = $model->slug;
        $form->is_variant = $model->is_variant;
        $form->sort_order = $model->sort_order;

        $data = $this->request->bodyParams;
        if (empty($data)) {
            $data = $this->request->post();
        }

        $form->load($data, '');

        if (!$form->validate()) {
            return $this->json(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->product_id = $form->product_id;
            $model->name = $form->name;
            $model->type = $form->type;
            $model->slug = $form->slug;
            $model->is_variant = $form->is_variant;
            $model->sort_order = $form->sort_order;

            if (!$model->save()) {
                $transaction->rollBack();
                return $this->json(false, $model->errors, 'Validation failed', 422);
            }

            if (is_array($form->attribute_value)) {
                AttributeValues::deleteAll(['attribute_id' => $model->id]);
                $this->saveAttributeValues($model->id, $form->attribute_value);
            }

            $transaction->commit();
            return $this->json(true, $model, 'Product attribute updated successfully');
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->json(false, null, 'Failed to save attribute values', 500);
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->json(true, null, 'Product attribute deleted successfully');
    }
    protected function findModel($id)
    {
        if (($model = ProductAttributes::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function saveAttributeValues(int $attributeId, array $values): void
    {
        foreach ($values as $index => $item) {
            $value = null;
            $colorHex = null;
            $sortOrder = $index;

            if (is_array($item)) {
                $value = $item['value'] ?? null;
                $colorHex = $item['color_hex'] ?? null;
                $sortOrder = $item['sort_order'] ?? $index;
            } elseif (is_string($item)) {
                $value = $item;
            }

            if ($value === null || $value == '') {
                throw new \RuntimeException('Attribute value is required.');
            }

            $attributeValue = new AttributeValues();
            $attributeValue->attribute_id = $attributeId;
            $attributeValue->value = $value;
            $attributeValue->color_hex = $colorHex;
            $attributeValue->sort_order = (int) $sortOrder;

            if (!$attributeValue->save()) {
                throw new \RuntimeException('Failed to save attribute value: ' . json_encode($attributeValue->errors));
            }
        }
    }
}

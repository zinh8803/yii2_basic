<?php

namespace app\controllers;

use app\models\AttributeValue;
use app\models\forms\product_attribute\ProductAttributeForm;
use app\models\ProductAttribute;
use app\models\search\ProductAttributeSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class ProductAttributeController extends BaseController
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
        $searchModel = new ProductAttributeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, 'Product attributes retrieved successfully');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Product attribute retrieved successfully');
    }

    public function actionCreate()
    {
        $this->checkPermission('productAttribute.create');
        $form = new ProductAttributeForm([
            'scenario' => ProductAttributeForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new ProductAttribute();
            $model->setAttributes($form->attributes, false);
            if (!$model->save(false)) {
                $transaction->rollBack();
                return $this->formatJson(false, $model->errors, 'Validation failed', 422);
            }

            if (is_array($form->attribute_value)) {
                $this->saveAttributeValues($model->id, $form->attribute_value);
            }

            $transaction->commit();
            return $this->formatJson(true, $model, 'Product attribute created successfully', 201);
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('productAttribute.update');
        $model = $this->findModel($id);
        $form = new ProductAttributeForm([
            'scenario' => ProductAttributeForm::SCENARIO_UPDATE,
        ]);
        $form->id = $model->id;
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->setAttributes($form->getAttributes([
                'product_id',
                'type',
                'name',
                'is_variant',
                'sort_order',
            ]), false);

            if (!$model->save(false)) {
                $transaction->rollBack();
                return $this->formatJson(false, $model->errors, 'Validation failed', 422);
            }

            if (is_array($form->attribute_value)) {
                AttributeValue::deleteAll(['attribute_id' => $model->id]);
                $this->saveAttributeValues($model->id, $form->attribute_value);
            }

            $transaction->commit();
            return $this->formatJson(true, $model, 'Product attribute updated successfully');
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }
    }

    public function actionDelete($id)
    {
        $this->checkPermission('productAttribute.delete');
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            AttributeValue::deleteAll(['attribute_id' => $model->id]);

            $model->delete();
            $transaction->commit();
            return $this->formatJson(true, null, 'Product attribute deleted successfully');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }
    }


    private function saveAttributeValues(int $attributeId, array $values): void
    {
        $rows = [];
        $time = time();

        foreach ($values as $index => $item) {
            $value = is_array($item) ? ($item['value'] ?? null) : $item;
            $colorHex = is_array($item) ? ($item['color_hex'] ?? null) : null;
            $sortOrder = is_array($item) ? ($item['sort_order'] ?? $index) : $index;

            if ($value === null || $value === '') {
                throw new \RuntimeException('Attribute value is required.');
            }

            $rows[] = [
                $attributeId,
                $value,
                $colorHex,
                (int) $sortOrder,
                \yii\helpers\Inflector::slug($value),
                $time,
                $time,
            ];
        }

        Yii::$app->db->createCommand()->batchInsert(
            AttributeValue::tableName(),
            ['attribute_id', 'value', 'color_hex', 'sort_order', 'slug', 'created_at', 'updated_at'],
            $rows
        )->execute();
    }

    public function findModel($id)
    {
        $model = ProductAttributeForm::find()
            ->where(['id' => $id])
            ->with('attributeValues')->one();
        if (!$model) {
            throw new NotFoundHttpException('Product attribute not found');
        }
        return $model;
    }
}

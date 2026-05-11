<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\CouponSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="coupons-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'code') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'value') ?>

    <?= $form->field($model, 'min_order_value') ?>

    <?php // echo $form->field($model, 'max_discount') ?>

    <?php // echo $form->field($model, 'max_usage') ?>

    <?php // echo $form->field($model, 'used_count') ?>

    <?php // echo $form->field($model, 'starts_at') ?>

    <?php // echo $form->field($model, 'expires_at') ?>

    <?php // echo $form->field($model, 'is_active') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'update_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

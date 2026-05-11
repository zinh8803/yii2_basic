<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Coupons $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="coupons-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'min_order_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'max_discount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'max_usage')->textInput() ?>

    <?= $form->field($model, 'used_count')->textInput() ?>

    <?= $form->field($model, 'starts_at')
        ->input('datetime-local') ?>

    <?= $form->field($model, 'expires_at')
        ->input('datetime-local') ?>

    <?= $form->field($model, 'is_active')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

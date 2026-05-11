<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\forms\OrderForm $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="orders-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'receiver_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'receiver_phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'receiver_address')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'is_discounted')->textInput() ?>

    <?= $form->field($model, 'shipping_fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'discount_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'payment_method')->textInput() ?>

    <?= $form->field($model, 'payment_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>


    <h3>Order Items</h3>
    <div id="order-items-wrapper">
        <?php
        $itemCount = is_array($model->item_product_id) ? count($model->item_product_id) : 1;
        $itemCount = max($itemCount, 1);
        for ($i = 0; $i < $itemCount; $i++):
            ?>
            <div class="order-item-row" data-index="<?= $i ?>">
                <div class="row">
                    <div class="col-md-4">
                        <?= Html::textInput("OrderForm[item_product_id][]", isset($model->item_product_id[$i]) ? $model->item_product_id[$i] : '', ['class' => 'form-control', 'placeholder' => 'Product ID']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= Html::textInput("OrderForm[item_variant_id][]", isset($model->item_variant_id[$i]) ? $model->item_variant_id[$i] : '', ['class' => 'form-control', 'placeholder' => 'Variant ID']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::textInput("OrderForm[item_quantity][]", isset($model->item_quantity[$i]) ? $model->item_quantity[$i] : '', ['class' => 'form-control', 'placeholder' => 'Quantity']) ?>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-remove-item"
                            onclick="removeOrderItemRow(this)">-</button>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <button type="button" class="btn btn-primary" onclick="addOrderItemRow()">+ Thêm sản phẩm</button>

    <script>
        function addOrderItemRow() {
            var wrapper = document.getElementById('order-items-wrapper');
            var index = wrapper.children.length;
            var row = document.createElement('div');
            row.className = 'order-item-row';
            row.setAttribute('data-index', index);
            row.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="OrderForm[item_product_id][]" class="form-control" placeholder="Product ID">
                </div>
                <div class="col-md-4">
                    <input type="text" name="OrderForm[item_variant_id][]" class="form-control" placeholder="Variant ID">
                </div>
                <div class="col-md-3">
                    <input type="text" name="OrderForm[item_quantity][]" class="form-control" placeholder="Quantity">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-item" onclick="removeOrderItemRow(this)">-</button>
                </div>
            </div>
        `;
            wrapper.appendChild(row);
        }
        function removeOrderItemRow(btn) {
            var row = btn.closest('.order-item-row');
            if (row) row.remove();
        }
    </script>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>


</div>
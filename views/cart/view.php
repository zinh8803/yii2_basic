<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\carts $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Carts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="carts-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'total',
            'created_at',
            'updated_at',
        ],
    ]) ?>

    <h2>Cart Items</h2>

    <?php if (empty($model->cartItems)): ?>
        <p>No items in cart.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Line Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->cartItems as $item): ?>
                    <tr>
                        <td><?= Html::encode($item->product ? $item->product->name : 'N/A') ?></td>
                        <td><?= Html::encode($item->productVariant ? $item->productVariant->name : '-') ?></td>
                        <td><?= Html::encode($item->price) ?></td>
                        <td>
                            <?= Html::beginForm(['update-item', 'id' => $item->id], 'post') ?>
                            <?= Html::hiddenInput('user_id', $model->user_id) ?>
                            <?= Html::input('number', 'quantity', $item->quantity, ['min' => 1, 'class' => 'form-control', 'style' => 'width: 90px; display: inline-block;']) ?>
                            <?= Html::submitButton('Update', ['class' => 'btn btn-sm btn-primary']) ?>
                            <?= Html::endForm() ?>
                        </td>
                        <td><?= Html::encode($item->price * $item->quantity) ?></td>
                        <td>
                            <?= Html::beginForm(['remove-item', 'id' => $item->id], 'post') ?>
                            <?= Html::hiddenInput('user_id', $model->user_id) ?>
                            <?= Html::submitButton('Remove', ['class' => 'btn btn-sm btn-danger']) ?>
                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
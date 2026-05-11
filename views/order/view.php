<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Orders $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="orders-view">

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
            'order_code',
            'stacking_id',
            'user_id',
            'email:email',
            'receiver_name',
            'receiver_phone',
            'receiver_address:ntext',
            'note:ntext',
            'is_discounted',
            'total',
            'shipping_fee',
            'discount_amount',
            'created_at',
            'updated_at',
            'payment_method',
            'payment_status',
            'status',
        ],
    ]) ?>

    <h2>Order Items</h2>

    <?php if (empty($model->orderItems)): ?>
        <p>No items in this order.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->orderItems as $item): ?>
                    <tr>
                        <td><?= Html::encode($item->product_name) ?></td>
                        <td><?= Html::encode($item->variant_name) ?></td>
                        <td><?= Html::encode($item->sku) ?></td>
                        <td><?= Html::encode($item->price) ?></td>
                        <td><?= Html::encode($item->quantity) ?></td>
                        <td><?= Html::encode($item->price * $item->quantity) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
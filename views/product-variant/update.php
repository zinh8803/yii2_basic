<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProductVariants $model */

$this->title = 'Update Product Variants: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Variants', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-variants-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\forms\Product\UpdateProductForm $model */
/** @var app\models\Products $product */

$this->title = 'Update Products: ' . $product->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $product->name, 'url' => ['view', 'id' => $product->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="products-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
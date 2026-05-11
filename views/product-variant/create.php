<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProductVariants $model */

$this->title = 'Create Product Variants';
$this->params['breadcrumbs'][] = ['label' => 'Product Variants', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-variants-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

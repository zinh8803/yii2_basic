<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProductAttributes $model */

$this->title = 'Create Product Attributes';
$this->params['breadcrumbs'][] = ['label' => 'Product Attributes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-attributes-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

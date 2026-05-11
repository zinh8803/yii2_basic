<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\forms\AddToCartForm $model */

$this->title = 'Create Carts';
$this->params['breadcrumbs'][] = ['label' => 'Carts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="carts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PostProducts $model */

$this->title = 'Update Post Products: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Post Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="post-products-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

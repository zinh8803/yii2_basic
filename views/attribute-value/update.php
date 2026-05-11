<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AttributeValues $model */

$this->title = 'Update Attribute Values: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Attribute Values', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="attribute-values-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

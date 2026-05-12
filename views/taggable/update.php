<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Taggables $model */

$this->title = 'Update Taggables: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Taggables', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="taggables-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

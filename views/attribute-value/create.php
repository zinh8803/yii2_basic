<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AttributeValues $model */

$this->title = 'Create Attribute Values';
$this->params['breadcrumbs'][] = ['label' => 'Attribute Values', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="attribute-values-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

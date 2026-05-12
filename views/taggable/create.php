<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Taggables $model */

$this->title = 'Create Taggables';
$this->params['breadcrumbs'][] = ['label' => 'Taggables', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="taggables-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PostProducts $model */

$this->title = 'Create Post Products';
$this->params['breadcrumbs'][] = ['label' => 'Post Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-products-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

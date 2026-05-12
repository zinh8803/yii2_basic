<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UserAddresses $model */

$this->title = 'Create User Addresses';
$this->params['breadcrumbs'][] = ['label' => 'User Addresses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-addresses-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

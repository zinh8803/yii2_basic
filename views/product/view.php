<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Products $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="products-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'category_id',
            'brand_id',
            'slug',
            'description',
            'status',
            'created_at',
            'updated_at',
        ],
    ]) ?>

    <?php $primaryImage = $model->resources[0] ?? null; ?>
    <?php if ($primaryImage !== null && $primaryImage->file): ?>
        <div>
            <h3>Image</h3>
            <?= Html::img($primaryImage->file->url, ['style' => 'max-width: 300px; height: auto;']) ?>
        </div>
    <?php endif; ?>

</div>

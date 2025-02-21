<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CategoryAttribute */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['category/index']];
$this->params['breadcrumbs'][] = ['label' => $model->category->title, 'url' => ['category/view', 'id' => $model->category->id]];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="category-attribute-view">

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'category_id',
                'value' => $model->category !== null ? Html::a($model->category->title, ['category/view', 'id' => $model->category->id]) : null,
                'format' => 'raw',
            ],
            'title',
            'title_ky',
            'description:html',
            [
                'attribute' => 'type',
                'value' => $model->typeTitle,
            ],
            [
                'attribute' => 'directory_id',
                'value' => $model->directory !== null ? Html::a($model->directory->title, ['directory/view', 'id' => $model->directory->id]) : null,
                'format' => 'raw',
            ],
            'is_required:boolean',
            'is_searchable:boolean',
            'is_searchable_price:boolean',
            'minimum',
            'maximum',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
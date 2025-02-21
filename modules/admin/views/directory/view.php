<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use himiklab\sortablegrid\SortableGridView;

/* @var $this yii\web\View */
/* @var $model app\models\Directory */
/* @var $directoryOptionSearchModel app\models\DirectoryOptionSearch */
/* @var $directoryOptionDataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="directory-view">

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Добавить элемент', ['directory-option/create', 'directory_id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            //'title_ky',
            'prompt',
            'prompt_ky',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h1>Элементы</h1>

    <?php Pjax::begin(); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $directoryOptionDataProvider,
        'columns' => [
            [
                'attribute' => 'value',
                'enableSorting' => false,
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['directory-option/view', 'id' => $model->id], [
                            'title' => 'Просмотр',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['directory-option/update', 'id' => $model->id], [
                            'title' => 'Редактировать',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['directory-option/delete', 'id' => $model->id], [
                            'title' => 'Удалить',
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                                'method' => 'post',
                            ],
                        ]);
                    }

                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
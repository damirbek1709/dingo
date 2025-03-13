<?php

use yii\widgets\DetailView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MeilisearchModel */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="meilisearch-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo $this->render('top_nav', ['model' => $model]); ?>
    <p>
        <?php //= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Delete', ['delete', 'id' => $model->id], [
'class' => 'btn btn-danger',
'data' => [
'confirm' => 'Are you sure you want to delete this item?',
'method' => 'post',
],
])*/ ?>
    </p>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>
            <div class="col-md-9">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        'name',
                        //'type',
                        'city',
                        //'address',
                        'currency',
                        //'features',
                        'phone',
                        [
                            'attribute' => 'site',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                return Html::a($model->site, $model->site, ['target' => '_blank']);
                                            },
                        ],
                        'check_in',
                        'check_out',
                        //'reception',
                        //'description:ntext',
                        'lat',
                        'lon',
                        // [
                        //     'attribute' => 'email',
                        //     'format' => 'raw',
                        //     'value' => function ($model) {
                        //                         return Html::mailto($model->email);
                        //                     },
                        // ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

</div>
<?php

use yii\helpers\Html;
use himiklab\sortablegrid\SortableGridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PaidServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Платные услуги';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paid-service-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'title',
                'enableSorting' => false,
            ],
            [
                'attribute' => 'price_per_day',
                'enableSorting' => false,
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

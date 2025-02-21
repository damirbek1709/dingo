<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\widgets\Select2;
use app\models\ComplaintType;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ComplaintTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Виды жалоб';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="complaint-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'title',
            [
                'attribute' => 'type',
                'value' => function (ComplaintType $data) {
                    return $data->typeString;
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'type',
                    'data' => ComplaintType::getTypeOptions(),
                    'options' => [
                        'placeholder' => 'Выберите тип ...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

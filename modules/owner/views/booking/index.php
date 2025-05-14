<?php

use app\models\Booking;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\BookingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Bookings');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-index col-md-12">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'columns' => [
            'guest_name',
            'date_from',
            'date_to',
            [
                'attribute'=>'room_id',
                'value'=>function($model){
                    return $model->bookingRoomTitle();
                }
            ],
            [
                'attribute'=>'object_id',
                'value'=>function($model){
                    return $model->bookingObjectTitle();
                }
            ],
            [
                'attribute'=>'tariff_id',
                'value'=>function($model){
                    return $model->bookingTariffTitle();
                }
            ],
            'special_comment',
            [
                'attribute'=>'sum',
                'value'=>function($model){
                    return $model->sum." ".$model->currency;
                }
            ],
            //'guest_email:email',
            //'guest_phone',
            //'guest_name',
            //'date_from',
            //
            //'status',
            //'other_guests',
            //'cancellation_type',
            //'cancellation_penalty_sum',
            //'user_id',
            //'special_comment',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Booking $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
            ],
        ],
    ]); ?>


</div>
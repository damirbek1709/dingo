<?php
use Yii;
use app\models\Booking;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Booking $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Bookings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="col-md-12">

    <div class="oblast-update">
        <?php //= $this->render('top_nav', ['object_id' => $model->object_id]) ?>
        <div class="booking-index">
            <?= DetailView::widget([
                'model' => $model,

                'attributes' => [
                    'guest_name',
                    [
                        'attribute' => 'date_from',
                        'value' => function ($model) {
                                        return $model->dateFormat($model->date_from);
                                    }
                    ],
                    [
                        'attribute' => 'date_to',
                        'value' => function ($model) {
                                        return $model->dateFormat($model->date_to);
                                    }
                    ],
                    [
                        'attribute' => 'room_id',
                        'value' => function ($model) {
                                        return $model->bookingRoomTitle();
                                    }
                    ],
                    [
                        'attribute' => 'object_id',
                        'value' => function ($model) {
                                        return $model->bookingObjectTitle();
                                    }
                    ],
                    [
                        'attribute' => 'tariff_id',
                        'value' => function ($model) {
                                        return $model->bookingTariffTitle();
                                    }
                    ],
                    'special_comment',
                    [
                        'attribute' => 'created_at',
                        'value' => function ($model) {
                                        return $model->dateFormat($model->created_at);
                                    }
                    ],
                    [
                        'attribute' => 'sum',
                        'value' => function ($model) {
                                        return $model->sum . " " . $model->currency;
                                    }
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                                        return $model->bookingStatusString()['string'];
                                    }
                    ],
                    'guest_email:email',
                    'guest_phone',
                    'other_guests',
                    [
                        [
                            'attribute' => 'refund_status',
                            'value' => $payment_status
                        ],
                    ]
                ],
            ]) ?>

            <div class="margin_30" style="margin-top:30px">
                <?php
                if ($model->status == Booking::PAID_STATUS_CANCELED) {
                    echo Html::a(Yii::t('app', 'Возврат средств'), ['refund', 'id' => $model->id], ['class' => 'save-button']);
                } ?>
            </div>
        </div>
    </div>
</div>
<?php
use app\models\Tariff;
use GuzzleHttp\Psr7\Uri;
use Yii;
use app\models\Booking;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;

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
                        'attribute' => 'refund_status',
                        'value' => $payment_status
                    ],

                ],
            ]) ?>

            <div class="margin_30" style="margin-top:30px">
                <?php
                if ($model->tariff->cancellation && $model->tariff->cancellation == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
                    if ($model->status == Booking::PAID_STATUS_CANCELED) {
                        echo Html::button(Yii::t('app', 'Возврат средств'), [
                            'class' => 'save-button btn-refund',
                            'data-id' => $model->id,
                        ]);
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'refund-modal',
    'header' => '<h4>' . Yii::t('app', 'Подтверждение возврата') . '</h4>',
    'footer' =>
        Html::button(Yii::t('app', 'Да, вернуть'), ['class' => 'btn btn-danger', 'id' => 'confirm-refund']) .
        Html::button(Yii::t('app', 'Отмена'), ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']),
]);

echo "<p>" . Yii::t('app', 'Вы уверены, что хотите вернуть средства?') . "</p>";
echo '<div id="refund-loading" style="display:none; text-align:center; margin-bottom: 15px;">' .
    Html::img('@web/images/site/loading.gif', ['alt' => 'Loading...', 'style' => 'width:50px;']) .
    '</div>';

Modal::end();
?>

<script>
    let refundId = null;

    $(document).on('click', '.btn-refund', function () {
        refundId = $(this).data('id');
        $('#refund-modal').modal('show');
    });

    $('#confirm-refund').on('click', function () {
        if (!refundId) return;

        $('#refund-loading').show(); // Show loading
        $('#confirm-refund').prop('disabled', true); // Disable button

        $.ajax({
            url: "<?=Yii::$app->urlManager->createUrl("/admin/booking/refund?id=$model->id")?>",
            type: 'POST',
            data: {
                id: refundId,
                _csrf: yii.getCsrfToken()
            },
            success: function (response) {
                $('#refund-loading').hide(); // Hide loading
                if (response.success) {
                    $('#refund-modal .modal-body').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#confirm-refund').hide();
                } else {
                    $('#refund-modal .modal-body').html('<div class="alert alert-danger">' + response.message + '</div>');
                    $('#confirm-refund').prop('disabled', false);
                }
            },
            error: function () {
                $('#refund-loading').hide(); // Hide loading
                $('#refund-modal .modal-body').html('<div class="alert alert-danger">Ошибка при возврате средств.</div>');
                $('#confirm-refund').prop('disabled', false);
            }
        });
    });

</script>
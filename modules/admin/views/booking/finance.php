<?php

use app\models\Booking;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\BookingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Финансы');
$this->params['breadcrumbs'][] = ['label' => 'Назад к списку', 'url' => ['/admin']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="oblast-update">

    <div class="search-filter-bar">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'options' => ['class' => 'search-filter-form'],
        ]); ?>

        <?php $form->end(); ?>

    </div>

    <div class="booking-index">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'summary' => false,
            'columns' => [
                [
                    'attribute' => 'object_id',
                    'value' => function ($model) {
                        return $model->bookingObjectTitle();
                    }
                ],
                [
                    'attribute' => 'owner_id',
                    'value' => function ($model) {
                        return $model->bookingOwnerTitle();
                    }
                ],

                [
                    'attribute' => 'tariff_id',
                    'value' => function ($model) {
                        return $model->bookingTariffTitle();
                    }
                ],
                [
                    'attribute' => 'date_range',
                    'format'=>'raw',
                    'value' => function ($model) {
                        return $model->dateFormat($model->date_from)." - <br>".$model->dateFormat($model->date_to);
                    }

                ],
                'transaction_number',
                'currency',
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

                [
                    'class' => ActionColumn::className(),
                    'template' => '{view}',
                    'urlCreator' => function ($action, Booking $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
                ],

            ],
        ]); ?>

    </div>
</div>

<?php $form = ActiveForm::begin([
    //'action' => ['booking/index', 'object_id' => $object_id],
    'method' => 'get',
    'options' => ['class' => 'search-filter-form'],
]);
?>


<?php $form->end(); ?>

<style>
    .search-filter-form {
        width: 100%;
    }

    .search-filter-bar {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 25px;
        background: #f9f9f9;
        font-family: sans-serif;
    }

    .search-box {
        position: relative;
        flex: 1;
    }

    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 16px;
        border: 1px solid #ddd;
        border-radius: 24px;
        font-size: 14px;
        outline: none;
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 16px;
    }

    .status-tabs {
        display: flex;
        background: rgba(245, 245, 245, 1);
        border-radius: 24px;
        overflow: hidden;
        width: 720px;
    }

    .status-tabs button {
        background: transparent;
        border: none;
        padding: 12px 16px;
        font-size: 14px;
        cursor: pointer;
        color: #666;
    }

    .filter-button {
        background-color: rgba(54, 118, 188, 1);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 24px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
    }



    .status-tabs a {
        color: #333333;
    }

    .<?= $active ?> {
        background: white !important;
        border-radius: 24px !important;
        color: black !important;
    }

    .filter-drawer {
        position: fixed;
        top: 70px;
        right: -500px;
        width: 500px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
        padding: 24px;
        transition: right 0.3s ease;
        z-index: 1000;
    }

    .filter-drawer.open {
        right: 0;
    }

    .filter-header {
        display: flex;
        align-items: center;
    }

    .filter-header h3 {
        margin: 0;
        font-size: 20px;
    }

    .close-filters {
        font-size: 24px;
        cursor: pointer;
        color: #888;
        margin-right: 10px;
    }

    .filter-body {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .filter-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 12px;
        font-size: 14px;
    }

    .status-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .status-tags button {
        padding: 6px 12px;
        border: 1px solid #ccc;
        background: white;
        border-radius: 16px;
        font-size: 14px;
        cursor: pointer;
    }

    .status-tags .active {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .reset-filters {
        margin-top: 12px;
        align-items: center;
        display: flex;
    }

    .reset-filters a {
        color: #2563eb;
        text-decoration: none;
        font-size: 14px;
    }

    .filter-body label {
        font-weight: normal;
        font-size: 16px;
        margin-bottom: 0;
    }

    .status-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .status-toggle {
        display: inline-block;
        position: relative;
    }

    .status-button {
        padding: 6px 14px;
        border: 1px solid #ccc;
        border-radius: 16px;
        cursor: pointer;
        font-size: 14px;
        color: #444;
        background: #fff;
        user-select: none;
    }

    .status-toggle input:checked+.status-button {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
</style>

<script>
    $(document).ready(function () {
        $('#open-filters').on('click', function () {
            $('#filterDrawer').addClass('open');
        });

        $('#closeFilters').on('click', function () {
            $('#filterDrawer').removeClass('open');
        });

        $('.status-tags button').on('click', function () {
            $(this).toggleClass('active');
        });

        $('#room-select').on('change', function () {
            let roomId = $(this).val();
            let objectId = $(this).data('object');

            if (roomId) {
                $('#tariff-select').attr('disabled', false);
                $.ajax({
                    url: "<?= Yii::$app->urlManager->createUrl('/owner/booking/get-tariffs'); ?>", // adjust if your route differs
                    type: 'GET',
                    data: {
                        room_id: roomId,
                        object_id: objectId
                    },
                    success: function (data) {
                        let $tariff = $('#tariff-select');
                        $tariff.empty();
                        $tariff.append('<option value="">Выберите тариф</option>');

                        $.each(data, function (key, value) {
                            $tariff.append('<option value="' + key + '">' + value + '</option>');
                        });
                    },
                    error: function () {
                        alert('Ошибка при загрузке тарифов');
                    }
                });
            } else {
                $('#tariff-select').html('<option value="">Выберите тариф</option>');
            }
        });

        $(document).on('click', '.status-button', function (e) {
            e.preventDefault(); // prevents unwanted form submission on accidental button clicks
            const $checkbox = $(this).siblings('input[type=checkbox]');
            const isChecked = $checkbox.prop('checked');

            $checkbox.prop('checked', !isChecked);
            $(this).toggleClass('active', !isChecked);
        });

        $('.reset-filter-link').on('click', function (e) {
            e.preventDefault();

            const $form = $(this).closest('form');

            // Reset all inputs
            $form.find('input[type="text"], input[type="date"], select').val('');
            // Uncheck all checkboxes and remove visual state
            $form.find('input[type="checkbox"]').each(function () {
                $(this).prop('checked', false);
            });
            // Remove active class from status buttons
            $form.find('.status-button').removeClass('active');
            // Reset tariff dropdown
            $('#tariff-select').html('<option value="">Выберите тариф</option>');
        });

        $(document).on('mouseup', function (e) {
            const $drawer = $('#filterDrawer');
            const $button = $('#open-filters');

            // If click is outside the drawer AND not the "open filters" button
            if (!$drawer.is(e.target) && $drawer.has(e.target).length === 0 &&
                !$button.is(e.target) && $button.has(e.target).length === 0) {
                $drawer.removeClass('open');
            }
        });
    });
</script>
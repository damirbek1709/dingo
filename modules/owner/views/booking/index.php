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

$this->title = Yii::t('app', 'Bookings');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-md-12">

    <div class="oblast-update">
        <?= $this->render('top_nav', ['object_id' => $object_id]) ?>
        <div class="search-filter-bar">
            <?php $form = ActiveForm::begin([
                'action' => ['booking/index', 'object_id' => $object_id],
                'method' => 'get',
                'options' => ['class' => 'search-filter-form'],
            ]); ?>
            <div class="search-box">
                <?= Html::textInput('guest_name', $guest_name, [
                    'placeholder' => 'Поиск по имени гостя',
                    'class' => 'guest-input'
                ]) ?>
                <button type="submit" class="search-icon"></button>
            </div>
            <?php $form->end(); ?>

            <div class="status-tabs">
                <button
                    class="future_active"><?= Html::a(Yii::t('app', 'Предстоящие'), ['/owner/booking', 'object_id' => $object_id, 'status' => 'future']) ?></button>
                <button
                    class="cancel_active"><?= Html::a(Yii::t('app', 'Отмененные'), ['/owner/booking', 'object_id' => $object_id, 'status' => 'canceled']) ?></button>
                <button
                    class="past_active"><?= Html::a(Yii::t('app', 'Завершенные'), ['/owner/booking', 'object_id' => $object_id, 'status' => 'past']) ?></button>
                <button
                    class="all_active"><?= Html::a(Yii::t('app', 'Все'), ['/owner/booking/index', 'object_id' => $object_id]) ?></button>
            </div>

            <button class="filter-button" id="open-filters">
                <?= Yii::t('app', 'Фильтры') ?>
            </button>
        </div>

        <div class="booking-index">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'summary' => false,
                'columns' => [
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
                        return $model->bookingStatusString()." ".$model->status;
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
                        'template' => '{view}',
                        'urlCreator' => function ($action, Booking $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
                    ],
                ],
            ]); ?>

        </div>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'action' => ['booking/index', 'object_id' => $object_id],
    'method' => 'get',
    'options' => ['class' => 'search-filter-form'],
]);
$room_list = Booking::getRoomList($object_id);
?>
<div class="filter-drawer" id="filterDrawer">
    <div class="filter-header">
        <span class="close-filters" id="closeFilters">&times;</span>
        <h3><?php echo Yii::t('app', 'Фильтры') ?></h3>
    </div>


    <div class="filter-body">
        <label><?= Yii::t('app', 'Номер') ?></label>
        <?= Html::dropDownList('room_id', $room_id, $room_list, [
            'prompt' => 'Выберите номер',
            'class' => 'filter-input',
            'id' => 'room-select',
            'data-object' => $object_id
        ]) ?>

        <label><?= Yii::t('app', 'Тариф') ?></label>
        <?php
        if ($room_id) {
            echo Html::dropDownList('tariff_id', $tariff_id, Booking::tariffList($object_id, $room_id), [
                'prompt' => 'Выберите тариф',
                'class' => 'filter-input',
                'id' => 'tariff-select',
                'disabled' => false
            ]);
        } else {
            echo Html::dropDownList('tariff_id', $tariff_id, [], [
                'prompt' => 'Выберите тариф',
                'class' => 'filter-input',
                'id' => 'tariff-select',
                'disabled' => true
            ]);
        }
        ?>

        <!-- <label><?php //echo Yii::t('app', 'Статус') ?></label> -->
        <?php
        // $statusOptions = [
        //     'active' => Yii::t('app', 'Активный'),
        //     'future' => Yii::t('app', 'Предстоящий'),
        //     'finished' => Yii::t('app', 'Завершен'),
        //     'canceled' => Yii::t('app', 'Отменен'),
        // ];
        ?>

        <!-- <?php //$selectedStatuses = Yii::$app->request->get('status', []); ?>

        <div class="status-tags">
            <?php //foreach ($statusOptions as $value => $label): ?>
                <label class="status-toggle">
                    <input type="checkbox" name="status[]" value="<?//= $value ?>" style="display: none;"
                        class="status-checkbox" <?//= in_array($value, $selectedStatuses) ? 'checked' : '' ?>>
                    <span class="status-button<?//= in_array($value, $selectedStatuses) ? ' active' : '' ?>">
                        <?//= $label ?>
                    </span>
                </label>
            <?php //endforeach; ?>
        </div> -->

        <label><?php echo Yii::t('app', 'Дата прибытия') ?></label>
        <input type="date" name="date_from" value="<?= $date_from ?>" class="filter-input" />

        <label><?php echo Yii::t('app', 'Дата выезда') ?></label>
        <input type="date" name="date_to" value="<?= $date_to ?>" class="filter-input" />

        <label><?php echo Yii::t('app', 'Дата бронирования') ?></label>
        <input type="date" name="book_date" value="<?= $date_book ?>" class="filter-input" />

        <div class="reset-filters">
            <button type="submit" class="save-button"
                style="margin:0 20px 0 0"><?php echo Yii::t('app', 'Применить') ?></button>

            <a href="#" class="reset-filter-link"><?php echo Yii::t('app', 'Сбросить все фильтры') ?></a>
        </div>
    </div>
</div>

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
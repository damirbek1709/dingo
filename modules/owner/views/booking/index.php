<?php

use app\models\Booking;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\StringHelper;
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
                        'format' => 'raw',
                        'value' => function ($model) {
                        $full = $model->bookingRoomTitle();
                        $cut = StringHelper::truncate($full, 20);
                        return "<span full={$full}>" . $cut . "</span>";
                    }
                    ],
                    [
                        'attribute' => 'object_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                        $full = $model->bookingObjectTitle();
                        $cut = StringHelper::truncate($full, 20);
                        return "<span full={$full}>" . $cut . "</span>";
                    }
                    ],
                    [
                        'attribute' => 'tariff_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                        $full = $model->bookingTariffTitle();
                        $cut = StringHelper::truncate($full, 20);
                        return "<span full={$full}>" . $cut . "</span>";
                    }
                    ],
                    [
                        'attribute' => 'special_comment',
                        'value' => function ($model) {
                        return $model->special_comment ? StringHelper::truncate($model->special_comment, 20) : "";
                    },
                        'format' => 'raw'
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
                        'format' => 'raw',
                        'value' => function ($model) {
                        $string = $model->bookingStatusString()["string"];
                        $color = $model->bookingStatusString()["color"];
                        return "<span class='status_td' style='color:{$color};border:1px solid {$color};'>" . $string . "</span>";
                    }
                    ],
                    [
                        'class' => ActionColumn::className(),
                        'template' => '{view}',
                        'header' => Yii::t('app', 'Действие'),
                        'buttons' => [
                            'view' => function ($url, $model) {
                            return Html::tag('span', 'Подробнее', [
                                'class' => 'table_action_button',
                                'guest_amount' => $model->guestAmount(),
                                'title' => Yii::t('app', 'Подробнее'),
                                'transaction_number' => $model->transaction_number,
                                'request-text' => $model->special_comment,
                                'color' => $model->bookingStatusString()["color"],
                                'status' => $model->bookingStatusString()["string"],
                                'cancel_text' => $model->cancelText(),
                                'cancel_date' => $model->cancel_date ? $model->dateFormat($model->cancel_date) : "",
                                'return_sum' => $model->sum - $model->cancellation_penalty_sum . " " . $model->currency,
                                'penalty_sum' => $model->cancellation_penalty_sum . " " . $model->currency,
                                'cancel_reason' => $model->cancel_reason_id ? $model->getCancelReasonArray()[0] : "",
                            ]);
                        },
                        ]
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
<?php

Modal::begin([
    'id' => 'booking-modal',
    //'size' => 'modal-md',
    'header' => '<h2 class="modal-title">Параметры бронирования</h2>',
    'options' => ['class' => 'modal-2'],
]); ?>
<div class="status-dialog-badge">В ожидании</div>
<div class="guest-info">

    <div class="guest-name">Асанова Алия</div>
</div>
<div class="booking-info">
    <div class="booking-info-item booking-info-head">Бронирование:</div>
    <div class="booking-info-item booking-info-room">2х мест, стандарт</div>
    <div class="booking-info-item booking-info-dates">28 мая 2025 - 30 мая 2025</div>
    <div class="booking-info-item booking-info-guests">2 гостя, 1 ребенок</div>
</div>

<div class="booking-details">
    <div class="detail-row">
        <span class="detail-label">Правила отмены:</span>
        <span class="detail-value detail-cancel-term">бесплатная отмена до 10 мая 2025</span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Стоимость</span>
        <span class="detail-value detail-price">19 000 KGS</span>
    </div>

    <div class="detail-row cancel_case" style="display:none">
        <div>
            <span class="detail-label">Дата отмены: </span>
            <span class="detail-value detail-cancel-date"></span>
        </div>

        <div>
            <span class="detail-label">Сумма возврата: </span>
            <span class="detail-value detail-return-sum"></span>
        </div>

        <div>
            <span class="detail-label">Сумма штрафа: </span>
            <span class="detail-value detail-penalty-sum"></span>
        </div>

        <div>
            <span class="detail-label">Причина отмены: </span>
            <span class="detail-value detail-cancel-reason"></span>
        </div>
    </div>

    <div class="detail-row">
        <span class="detail-label">Дата бронирования: </span>
        <span class="detail-value detail-book-date">28 мая 2025</span>
    </div>

    <div class="detail-row">
        <span class="detail-label">№ Бронирования </span>
        <span class="detail-value detail-transaction-number">123434574573</span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Тариф </span>
        <span class="detail-value detail-tariff">Все включено</span>
    </div>
</div>

<div class="special-requests">
    <h3 class="section-title">Особые пожелания</h3>
    <p class="request-text">Ранний заезд в 11:00 и выезд в 19:00?</p>

    <textarea class="message-input" placeholder="Напишите вашему гостю о вашем решении"></textarea>
</div>

<button class="send-button">Отправить</button>
<div style="clear: both;"></div>

<?php Modal::end(); ?>



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

    $(document).on('click', '.table_action_button', function () {
        var name = $(this).parent().siblings().first().text();
        var room = $(this).parent().siblings().eq(3).text();
        var dates = $(this).parent().siblings().eq(1).text() + " - " + $(this).parent().siblings().eq(2).text();
        var guests = $(this).attr('guest_amount') + " гостя";
        var price = $(this).parent().siblings().eq(8).text();
        var tariff = $(this).parent().siblings().eq(5).text();
        var book_date = $(this).parent().siblings().eq(7).text();
        var color = $(this).attr('color');
        var status = $(this).attr('status');


        $('.guest-name').text(name);
        $('.booking-info-room').text(room);
        $('.booking-info-dates').text(dates);
        $('.booking-info-guests').text(guests);
        $('.status-dialog-badge').css('border', '1px solid ' + color).css('color', color);
        $('.status-dialog-badge').text(status);

        $('.detail-transaction-number').text($(this).attr('transaction_number'));

        $('.detail-price').text(price);
        $('.detail-book-date').text(book_date);
        $('.detail-tariff').text(tariff);
        $('.request-text').text($(this).attr('request-text'));
        $('.detail-cancel-term').text($(this).attr('cancel_text'));



        if (status == "Отменен") {
            $('.cancel_case').css("display", "block");
            $('.detail-cancel-date').text($(this).attr('cancel_date'));
            $('.detail-return-sum').text($(this).attr('return_sum'));
            $('.detail-penalty-sum').text($(this).attr('penalty_sum'));
            $('.detail-cancel-reason').text($(this).attr('cancel_reason'));
        }
        else {
            $('.cancel_case').css("display", "none");
        }
        $('#booking-modal').modal('show');
    });

    document.querySelector('.close-btn').addEventListener('click', function () {
        document.body.style.display = 'none';
    });

    // Send button functionality
    document.querySelector('.send-button').addEventListener('click', function () {
        const message = document.querySelector('.message-input').value;
        if (message.trim()) {
            alert('Сообщение отправлено: ' + message);
            document.querySelector('.message-input').value = '';
        } else {
            alert('Пожалуйста, введите сообщение');
        }
    });

    // Click outside to close
    document.addEventListener('click', function (e) {
        if (e.target === document.body) {
            document.body.style.display = 'none';
        }
    });
</script>

<style>
    .status_td {
        border-radius: 4px;
        padding: 2px 4px;
        display: block;
        min-width: 90px;
        text-align: center;
        cursor: pointer;
    }

    .booking-info-head {
        color: rgba(0, 0, 0, 0.45) !important;
    }

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

    .modal-body {
        padding: 0 !important;
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

    .detail-row div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
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

    .table_action_button {
        color: rgba(54, 118, 188, 1);
        border: 1px solid rgba(54, 118, 188, 1);
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        position: relative;
        padding: 15px 0;
        border-bottom: unset;
    }

    .modal-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background: #f0f0f0;
        color: #666;
    }

    .status-dialog-badge {
        display: inline-block;
        background: rgb(253 253 253);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 12px;
    }

    .guest-info {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .guest-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #E5E7EB;
        overflow: hidden;
    }

    .guest-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .guest-name {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    .modal-content {
        padding: 0 24px 24px;
    }

    .booking-details {
        margin-bottom: 24px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #F3F4F6;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #6B7280;
        font-size: 14px;
    }

    .detail-value {
        color: #111827;
        font-weight: 500;
        text-align: right;
    }

    .booking-info {
        margin-bottom: 24px;
    }

    .booking-info-item {
        color: #374151;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .booking-info-item:last-child {
        margin-bottom: 0;
    }

    .special-requests {
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 12px;
    }

    .request-text {
        color: #6B7280;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .message-input {
        width: 100%;
        border: 1px solid #D1D5DB;
        border-radius: 12px;
        padding: 16px;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    .message-input:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .message-input::placeholder {
        color: #9CA3AF;
    }

    .send-button {
        background: #3B82F6;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 16px 32px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        float: right;
        transition: all 0.2s;
    }

    .send-button:hover {
        background: #2563EB;
        transform: translateY(-1px);
    }

    .send-button:active {
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .modal-container {
            width: 95%;
            margin: 20px;
        }

        .modal-header,
        .modal-content {
            padding-left: 16px;
            padding-right: 16px;
        }

        .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .detail-value {
            text-align: left;
        }
    }
</style>
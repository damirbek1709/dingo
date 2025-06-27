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
    <div class="stats-grid">
        <div class="stat-card">

            <div class="stat-content">
                <div class="label">Общий оборот</div>
                <h3><?= Booking::totalPayments(); ?></h3>
                <div class="stat-change positive">↗ 8.5% чем за прошлый месяц</div>
            </div>
            <div class="stat-icon calendar">📅</div>
        </div>
        <div class="stat-card">

            <div class="stat-content">
                <div class="label">Выплаты хостам</div>
                <h3>1 056 418</h3>
                <div class="stat-change positive">↗ 8.5% чем за прошлый месяц</div>
            </div>
            <div class="stat-icon host">🏠</div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="label">Комиссия платформы</div>
                <h3><?=Booking::totalComission()?></h3>
                <div class="stat-change negative">↘ 4.3% чем за прошлый месяц</div>
            </div>
            <div class="stat-icon commission">💰</div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="label">Возвраты</div>
                <h3>
                    <?= Booking::totalRefunds(); ?>
                    <!-- <span class="currency">KGS</span> -->
                </h3>
                <div class="stat-change negative">↘ 4.3% чем за прошлый месяц</div>
            </div>
            <div class="stat-icon refund">↩️</div>
        </div>
    </div>

    <!-- Controls -->
    <div class="controls">
        <div class="search-box">
            <input type="text" placeholder="Поиск по № брони и названию объекта">
        </div>
        <div class="date-range">
            08 Авг 2025 → 10 Сен 2025 📅
        </div>
        <button class="btn btn-secondary">Импорт в Excel</button>
        <button class="btn btn-primary">⚙️ Фильтры</button>
    </div>

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
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->dateFormat($model->date_from) . " - <br>" . $model->dateFormat($model->date_to);
                    }

                ],
                'transaction_number',
                'currency',
                [
                    'attribute' => 'payment_type',
                    'value' => function ($model) {
                        return "<span class='payment_type'>" . $model->payment_type . "</span>";
                    },
                    'format' => 'raw'
                ],

                [
                    'attribute' => 'sum',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->sum . "<br>" . $model->currency;
                    }
                ],
                [
                    'attribute' => 'return_status',
                    'format' => 'raw',
                    'label' => 'Статус',
                    'value' => function ($model) {
                        $color = $model->refundStatusString()['color'];
                        return "<div style='color:$color;border:1px solid $color;display:inline-block;background-color:rgba(113, 111, 243, 0.05);padding:2px 3px;border-radius:4px'><span>" . $model->refundStatusString()['string'] . "</span></div>";
                    }
                ],
                [
                    'class' => ActionColumn::className(),
                    'template' => '{view}',
                    'header' => Yii::t('app', 'Действие'),
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::tag('span', $model->refundStatusString()['action_string'], [
                                'class' => 'table_action_button payback',
                                'action' => $model->refundStatusString()['action']
                            ]);
                        },
                    ]
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
    .payback {
        background-color: #3676BC;
        color: #fff;
        padding: 3px 5px;
        border-radius: 4px;
        cursor: pointer;
    }

    .payment_type {
        text-transform: uppercase;
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

    .dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        /* align-items: center; */
        gap: 15px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-left: auto;
    }

    .stat-icon.calendar {
        background-color: #e8f5e8;
        color: #4a7c59;
    }

    .stat-icon.host {
        background-color: #fff3cd;
        color: #856404;
    }

    .stat-icon.commission {
        background-color: #cce7ff;
        color: #0066cc;
    }

    .stat-icon.refund {
        background-color: #e6e6fa;
        color: #6a5acd;
    }

    .stat-content h3 {
        font-size: 24px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .stat-content .label {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
        font-weight: 400;
        padding: 0;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.positive {
        color: #22c55e;
    }

    .stat-change.negative {
        color: #ef4444;
    }

    /* Controls */
    .controls {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 16px;
        border: 1px solid #e1e5e9;
        border-radius: 8px;
        font-size: 14px;
        background: white;
    }

    .search-box::after {
        content: "🔍";
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .date-range {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        color: #333;
    }

    .btn {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #2563eb;
        color: white;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        background: white;
        color: #2563eb;
        border: 1px solid #e1e5e9;
    }

    .btn-secondary:hover {
        background: #f8fafc;
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f8f9fa;
        padding: 16px 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 16px 12px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        vertical-align: middle;
    }

    tr:hover {
        background: #f9fafb;
    }

    .property-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .property-type {
        color: #6b7280;
        font-size: 12px;
    }

    .guest-name {
        font-weight: 500;
        color: #1f2937;
    }

    .tariff-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .tariff-non-refundable {
        background: #fee2e2;
        color: #dc2626;
    }

    .tariff-refundable {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
    }

    .status-awaiting {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-awaiting-payment {
        background: #fef3c7;
        color: #92400e;
    }

    .status-upcoming {
        background: #dcfce7;
        color: #166534;
    }

    .amount {
        font-weight: 600;
        color: #1f2937;
    }

    .currency {
        color: #6b7280;
        font-size: 12px;
        margin-left: 4px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-payout {
        background: #2563eb;
        color: white;
    }

    .btn-payout:hover {
        background: #1d4ed8;
    }

    .btn-details {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-details:hover {
        background: #e5e7eb;
    }

    @media (max-width: 768px) {
        .controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: auto;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            min-width: 800px;
        }
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
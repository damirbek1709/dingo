<?php

use app\models\Booking;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
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

            <div class="status-tabs">
                <button
                    class="future_active"><?= Html::a(Yii::t('app', 'Продажи'), ['/sales', 'object_id' => $object_id, 'status' => 'future']) ?></button>
                <button
                    class="cancel_active"><?= Html::a(Yii::t('app', 'Выплаты'), ['/payments/booking', 'object_id' => $object_id, 'status' => 'canceled']) ?></button>
                <button
                    class="past_active"><?= Html::a(Yii::t('app', 'Реквизиты'), ['/owner/booking', 'object_id' => $object_id, 'status' => 'past']) ?></button>
            </div>


        </div>

        <?php $form = ActiveForm::begin([
            //'action' => ['booking/finance'],
            'method' => 'get',
            'options' => ['id' => 'search-filter-form'],
        ]); ?>
        <div class="controls">

            <div class="date-range-container">
                <div class="date-range-inputs">
                    <input lang="ru" type="date" name="date_from" id="checkin" class="date-input"
                        value="<?= $date_from_string ? $date_from_string : 'От' ?>">

                    <span class="date-separator">→</span>

                    <input lang="ru" type="date" name="date_to" id="checkout" class="date-input"
                        value="<?= $date_to_string ? $date_to_string : 'До' ?>">

                    <span class="calendar-icon"></span>
                </div>
            </div>


            <div class="tag-container">
                <?= Html::tag('span', Yii::t('app', 'Количество') . ": " . $amount, ['object_id' => $object_id, 'class' => 'amount-tag']) ?>
                <?= Html::tag('span', Yii::t('app', 'Доход') . ": " . $income, ['object_id' => $object_id, 'class' => 'amount-tag']) ?>
                <?= Html::tag('span', Yii::t('app', 'Коммиссия') . ": " . $comission, ['object_id' => $object_id, 'class' => 'amount-tag']) ?>
                <?= Html::tag('span', Yii::t('app', 'К выплате') . ": " . $payment, ['object_id' => $object_id, 'class' => 'amount-tag']) ?>
            </div>
        </div>
        <?php $form->end(); ?>

        <div class="booking-index">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'summary' => false,
                'columns' => [
                    //'owner_id',
                    // [
                    //     'attribute' => 'date_from',
                    //     'value' => function ($model) {
                    //     return $model->dateFormat($model->date_from);
                    // }
                    // ],
                    // [
                    //     'attribute' => 'date_to',
                    //     'value' => function ($model) {
                    //     return $model->dateFormat($model->date_to);
                    // }
                    // ],
                    [
                        'attribute' => 'owner_id',
                        'value' => function ($model) {
                                return Yii::$app->user->identity->name;
                            },
                        'label' => Yii::t('app', 'ФИО')
                    ],
                    [
                        'attribute' => 'room_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                                $full = $model->bookingRoomTitle();
                                $cut = StringHelper::truncate($full, 20);
                                return "<span class='grid_short' full='$full'>" . $cut . "</span>";
                            }
                    ],
                    [
                        'attribute' => 'transaction_number',
                        'value' => function ($model) {
                                return $model->transaction_number;
                            },
                        'label' => '№ Бронирования',
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function ($model) {
                                return $model->dateFormat($model->created_at);
                            }
                    ],
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
                    //'special_comment',
            
                    [
                        'attribute' => 'sum',
                        'value' => function ($model) {
                                return $model->sum . " " . $model->currency;
                            }
                    ],
                    [
                        'attribute' => 'comission',
                        'value' => function ($model) {
                                return $model->comissionFee();
                            }
                    ],
                    [
                        'label' => Yii::t('app', 'К выплате'),
                        'attribute' => 'income',
                        'value' => function ($model) {
                                return $model->incomeString();
                            }
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                                $string = $model->refundStatusString()["string"];
                                $color = $model->refundStatusString()["color"];
                                return "<span class='status_td' style='color:{$color};border:1px solid {$color};'>" . $string . "</span>";
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



    <style>
        .status_td {
            border-radius: 4px;
            padding: 2px 4px;
            display: block;
            min-width: 90px;
            text-align: center;
            cursor: pointer;
        }

        .search-filter-form {
            width: 100%;
        }

        .search-filter-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 25px;
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

        .controls {
            margin: 30px 0;
            display: flex;
        }

        .tag-container {
            display: flex;
        }

        .amount-tag {
            background-color: #fff;
            border-radius: 20px;
            border: none;
            box-shadow: 0px 2px 4px 0px #00000005;
            box-shadow: 0px 1px 6px -1px #00000005;
            box-shadow: 0px 1px 2px 0px #00000008;
            height: 100%;
            padding: 10px 25px;
            margin-left: 15px;
        }

        .status-tabs {
            display: flex;
            background: rgba(245, 245, 245, 1);
            border-radius: 24px;
            overflow: hidden;
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

        .grid_short {
            position: relative;
            cursor: pointer;
        }

        .grid_short::after {
            content: attr(full);
            position: absolute;
            white-space: pre-wrap;
            bottom: 125%;
            /* Position above */
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 8px 10px;
            border-radius: 5px;
            font-size: 14px;
            /* Bigger font */
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            z-index: 100;
            width: max-content;
            max-width: 300px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .grid_short:hover::after {
            opacity: 1;
        }

        .date-range-container {
            font-family: sans-serif;
            max-width: 400px;
        }

        .date-range-label {
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }

        .date-range-inputs {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 12px;
            background: #fff;
        }

        .date-input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            color: #555;
            width: 100%;
        }

        .date-separator {
            margin: 0 8px;
            color: #999;
            font-size: 16px;
        }

        .calendar-icon {
            margin-left: 8px;
            color: #aaa;
            font-size: 16px;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
        }

        input[type="date"] {
            position: relative;
            cursor: pointer;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: auto;
            height: auto;
            color: transparent;
            background: transparent;
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInputs = document.querySelectorAll('.date-input');
            const form = document.getElementById('search-filter-form');

            dateInputs.forEach(function (input) {
                input.addEventListener('change', function () {
                    form.submit();
                });
            });
        });
    </script>
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
<div class="col-md-12">

    <div class="oblast-update">
        <?= $this->render('top_nav', ['object_id' => $object_id]) ?>
        <div class="booking-index">
            <h1><?= Html::encode($this->title) ?></h1>
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
                                        return $model->bookingStatusString();
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
<style>
    .search-container {
        position: relative;
        flex: 0 1 400px;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: 9999px;
        font-size: 0.875rem;
        color: var(--text-gray);
        background-color: var(--bg-white);
        outline: none;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        box-shadow: 0 0 0 3px var(--focus-ring);
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-gray);
        width: 1rem;
        height: 1rem;
        pointer-events: none;
    }

    /* Tab navigation */
    .tabs {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .tab {
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: var(--inactive-tab);
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .tab:hover {
        color: var(--active-tab);
    }

    .tab.active {
        color: var(--active-tab);
        border-bottom-color: var(--active-tab);
        font-weight: 500;
    }

    /* Filter button */
    .filter-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .filter-button:hover {
        background-color: #2563eb;
    }

    .filter-icon {
        width: 1rem;
        height: 1rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            gap: 1rem;
        }

        .search-container {
            width: 100%;
            flex: 1;
        }

        .tabs {
            width: 100%;
            justify-content: space-between;
        }
</style>
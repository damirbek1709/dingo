<?php

use app\models\RoomCat;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\RoomCatSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Room Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-cat-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Room Category'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'columns' => [
            'id',
            'title_ky',
            'guest_amount',
            'similar_room_amount',
            'area',
            [
                'attribute' => 'bathroom',
                'value' => function ($model) {
                        if ($model->bathroom) {
                            return "Да";
                        } else {
                            return "Нет";
                        }
                    },
            ],
            [
                'attribute' => 'balcony',
                'value' => function ($model) {
                        if ($model->balcony) {
                            return "Да";
                        } else {
                            return "Нет";
                        }
                    },
            ],
            [
                'attribute' => 'balcony',
                'value' => function ($model) {
                        if ($model->air_cond) {
                            return "Да";
                        } else {
                            return "Нет";
                        }
                    },
            ],
            [
                'attribute' => 'balcony',
                'value' => function ($model) {
                        if ($model->kitchen) {
                            return "Да";
                        } else {
                            return "Нет";
                        }
                    },
            ],
            'base_price',
            //'img',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, RoomCat $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'id' => $model->id]);
                    }
            ],
        ],
    ]); ?>


</div>
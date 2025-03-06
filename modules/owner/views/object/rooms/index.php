<?php

use app\models\RoomCat;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var app\models\RoomCatSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Room Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-cat-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('../top_nav', ['model' => $model]); ?>
    <p style="float:right">
        <?= Html::a(Yii::t('app', 'Добавить номер'), ['add-room', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Создать тариф'), ['add-tariff', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    /** @var yii\web\View $this */
    echo ListView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'itemView' => '_item',  // Partial view for rendering each item
        //'layout' => "{summary}\n{items}\n{pager}",  // Optional: customize layout
    ]);
    ?>


</div>
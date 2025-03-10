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

    <h1><?= Html::encode($model->name) ?></h1>

    <?php echo $this->render('../top_nav', ['model' => $model]); ?>
    <p style="float:right">
        <?= Html::a(Yii::t('app', 'Добавить номер'), ['add-room', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Создать тариф'), ['add-tariff', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="room_list clear">
        <?php foreach ($rooms as $key => $val) {
            echo Html::beginTag('div', []);
            echo Html::tag('div', $val['room_title']);
            echo Html::tag('div', "Количество людей: " . $val['guest_amount']);
            echo Html::endTag('div');
        }
        ?>
    </div>
</div>

<?php
echo "<pre>";
print_r($rooms);
echo "</pre>";
?>
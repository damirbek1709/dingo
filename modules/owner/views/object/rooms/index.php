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

$this->title = Yii::t('app', 'Список номеров');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Объекты'), 'url' => ['object/view', 'id' => $object_id]];
$this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['view', 'id' => $object_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-cat-index">

    <h1><?= Html::encode($object_title) ?></h1>

    <?php echo $this->render('../top_nav', ['model' => $model]); ?>
    <p style="float:right">
        <?= Html::a(Yii::t('app', 'Добавить номер'), ['add-room', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Создать тариф'), ['add-tariff', 'object_id' => $object_id], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="room_list clear">
        <?php foreach ($rooms as $key => $val): ?>
            <a href="room?id=<?= $val['id'] ?>&object_id=<?= $object_id ?>">
                <div class="room_list_owner">
                    <img src="<?= $val['images'][0]['thumbnailPicture'] ?? 'default.jpg' ?>" alt="Room Image">
                    <div class="room_list_info">
                        <div class="title"> <?= $val['room_title'] ?> </div>
                        <div class="details">
                            <span class="room_area"><?= $val['area'] . " " . Yii::t('app', 'м²'); ?></span>
                            <span class="room_guest_amount"><?= Yii::t('app', 'Количество людей') ?>:
                                <?= $val['guest_amount'] ?>
                            </span>
                            <span class="room_tariff">
                                <?= Html::a(Yii::t('app', 'Привязанные тарифы'), ['tariff-list', 'object_id' => $object_id, 'room_id' => $val['id']]) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

</div>
<?php
use yii\helpers\Html;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Информация'), ['room', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Фотографии'), ['pictures', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-beds">
        <?= Html::a(Yii::t('app', 'Спальные места'), ['room-beds', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>


    <div class="owner-nav-item owner-nav-item-comfort">
        <?= Html::a(Yii::t('app', 'Удобства'), ['room-comfort', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

   
</div>
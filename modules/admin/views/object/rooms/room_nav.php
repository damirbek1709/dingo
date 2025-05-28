<?php
use yii\helpers\Html;
$nav_action_class = Yii::$app->controller->action->id;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item nav_room">
        <?= Html::a(Yii::t('app', 'Информация'), ['room', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

    <div class="owner-nav-item nav_pictures">
        <?= Html::a(Yii::t('app', 'Фотографии'), ['pictures', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-beds nav_room-beds">
        <?= Html::a(Yii::t('app', 'Спальные места'), ['room-beds', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-comfort nav_room-comfort">
        <?= Html::a(Yii::t('app', 'Удобства'), ['room-comfort', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>
</div>

<style>
.nav_<?=$nav_action_class?> a {
    color:#3676BC!important;
}

.nav_<?=$nav_action_class?>:before{
    filter: invert(27%) sepia(85%) saturate(1200%) hue-rotate(200deg) brightness(95%) contrast(95%)!important;
}
</style>
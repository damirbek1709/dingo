<?php
use yii\helpers\Html;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Информация'), ['room', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>
    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['room-comfort', 'id' => $room_id, 'object_id'=>$object_id]); ?>
    </div>

   
</div>
<?php
use yii\helpers\Html;
?>

<div class="top_nav">
    <?php if (isset($object_id)) {
        $model->id = $object_id;
    } ?>
    <?= Html::a('Отель', ['view', 'object_id' => $model->id], ['class' => 'top_nav_title top_nav_title_hotel']); ?>
    <?= Html::a('Доступность и цены', ['prices', 'object_id' => $model->id], ['class' => 'top_nav_title top_nav_title_prices']); ?>
    <?= Html::a('Бронирования', ['booking', 'object_id' => $model->id], ['class' => 'top_nav_title top_nav_title_booking']); ?>
    <?= Html::a('Номера и тарифы', ['room-list', 'object_id' => $model->id], ['class' => 'top_nav_title top_nav_title_rooms']); ?>
    <?= Html::a('Финансы', ['finance', 'object_id' => $model->id], ['class' => 'top_nav_title top_nav_title_finance']); ?>
</div>
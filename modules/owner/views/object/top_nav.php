<?php
use yii\helpers\Html;
?>

<div class="top_nav">
    <?= Html::a('Отель', ['view', 'id' => $model->id], ['class' => 'top_nav_title']); ?>
    <?= Html::a('Доступность и цены', ['prices', 'id' => $model->id], ['class' => 'top_nav_title']); ?>
    <?= Html::a('Номера и тарифы', ['room-list', 'id' => $model->id], ['class' => 'top_nav_title']); ?>
</div>
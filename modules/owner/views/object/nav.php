<?php
use yii\helpers\Html;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item owner-nav-item-info">
        <?= Html::a(Yii::t('app', 'Информация'), ['view', 'object_id' => $model->id]); ?>
    </div>
    <div class="owner-nav-item owner-nav-item-comfort">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['comfort', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-payment">
        <?= Html::a(Yii::t('app', 'Оплата'), ['payment', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-terms">
        <?= Html::a(Yii::t('app', 'Условия'), ['terms', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-crew">
        <?= Html::a(Yii::t('app', 'Сотрудники'), ['crew', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-feedback">
        <?= Html::a(Yii::t('app', 'Отзывы'), ['feedback', 'object_id' => $model->id]); ?>
    </div>
</div>
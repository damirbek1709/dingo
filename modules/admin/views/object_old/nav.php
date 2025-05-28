<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\models\Objects;
use yii\widgets\Pjax;
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

    <!-- <div class="owner-nav-item owner-nav-item-crew">
        <?php //echo Html::a(Yii::t('app', 'Сотрудники'), ['crew', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-feedback">
        <?php //echo  Html::a(Yii::t('app', 'Отзывы'), ['feedback', 'object_id' => $model->id]); ?>
    </div> -->
</div>


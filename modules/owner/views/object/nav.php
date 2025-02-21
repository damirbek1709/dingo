<?php
use yii\helpers\Html;
?>
<div class="owner-nav-cover row">
    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Информация'), ['view', 'id' => $model->id]); ?>
    </div>
    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['comfort', 'id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Оплата'), ['payment', 'id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Условия'), ['terms', 'id' => $model->id]); ?>
    </div>
</div>
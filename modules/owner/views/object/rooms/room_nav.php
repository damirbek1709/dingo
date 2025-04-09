<?php
use yii\helpers\Html;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Информация'), ['view', 'id' => $model->id]); ?>
    </div>
    <div class="owner-nav-item">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['room-comfort', 'id' => $model->id, 'object_id'=>$model->object_id]); ?>
    </div>

   
</div>
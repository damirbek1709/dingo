<?php
use yii\helpers\Html;
if ($model['images']) {
    echo Html::img($model['images'][0]);
}
echo $model['room_title'];
?>

<div class="room-card">
    <img src="<?=$model['images'][0];?>" alt="Room Image">
    <div class="room-card-details">
        <h3><?=$model['room_title'];?></h3>
        <div class="room-info">
            <span><?=$model['area']?> м²</span> | <span>2 взрослых</span>
        </div>
        <div class="bed-info"><?php //$model['bed_types']?></div>
    </div>
    <div class="room-card-actions">
        <span class="tariff-label"><?php Yii::t('app', 'Привязать тариф');?></span>
        <button class="button"><?php echo Yii::t('app', 'Привязать');?></button>
    </div>
</div>
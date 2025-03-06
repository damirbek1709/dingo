<?php
use yii\helpers\Html;
if ($model['images']) {
    echo Html::img($model['images'][0]);
}
echo $model['room_title'];
?>
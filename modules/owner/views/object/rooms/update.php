<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */

$this->title = Yii::t('app', 'Update Room Category') . " " . $model->room_title;
$this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['object/view', 'id' => $model->id,'object_id'=>$object_id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Список номеров'), 'url' => ['room-list', 'id' => $object_id]];

$this->params['breadcrumbs'][] = ['label' => $model->room_title, 'url' => ['room', 'id' => $model->id,'object_id'=>$object_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="room-cat-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'bindModel' => $bindModel
    ]) ?>

</div>
<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RoomType $model */

$this->title = Yii::t('app', 'Create Room Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Room Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

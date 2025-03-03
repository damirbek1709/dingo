<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */

$this->title = Yii::t('app', 'Create Room Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Room Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-cat-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'id'=> $id
    ]) ?>

</div>

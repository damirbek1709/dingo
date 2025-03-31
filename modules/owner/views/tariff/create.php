<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = Yii::t('app', 'Создание нового тарифа');
$this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['view', 'id' => $object_id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Номера и тарифы'), 'url' => ['room-list', 'id' => $object_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariff-create">

    <h2><?= Html::encode($this->title) ?></h2>
    <?= $this->render('_form', [
        'model' => $model,
        'object_id' => $object_id
    ]) ?>

</div>
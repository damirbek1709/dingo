<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = Yii::t('app', 'Update Tariff: {name}', [
    'name' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tariffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="tariff-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'object_id' => $object_id,
    ]) ?>

</div>

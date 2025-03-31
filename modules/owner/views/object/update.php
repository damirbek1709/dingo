<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$index = 0;
$name_arr = $model->name;
$name = $name_arr[$index];

$this->title = Yii::t('app', 'Update Object') . ' ' . $name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="oblast-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form_update', [
        'model' => $model,
    ]) ?>

</div>
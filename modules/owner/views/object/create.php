<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Create Object') . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Создать объект');
?>
<div class="oblast-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
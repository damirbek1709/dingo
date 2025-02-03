<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Features $model */

$this->title = Yii::t('app', 'Update Features').": ". $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Features'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="features-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = Yii::t('app', 'Создание нового тарифа');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tariffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariff-create">

    <h2><?= Html::encode($this->title) ?></h2>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
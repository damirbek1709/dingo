<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PaymentType $model */

$this->title = Yii::t('app', 'Create Payment Type');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Payment Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

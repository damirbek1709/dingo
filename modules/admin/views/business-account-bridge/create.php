<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BusinessAccountBridge */

$this->title = Yii::t('app', 'Create Business Account Bridge');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Business Account Bridges'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-account-bridge-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

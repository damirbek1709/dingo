<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PaidService */

$this->title = 'Добавление';
$this->params['breadcrumbs'][] = ['label' => 'Платные услуги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paid-service-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\NotificationList $model */

$this->title = Yii::t('app', 'Create Notification List');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Notification Lists'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notification-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

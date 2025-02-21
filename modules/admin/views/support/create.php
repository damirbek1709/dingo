<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Support */

$this->title = Yii::t('app', 'Добавить вопрос');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Тех.поддержка'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="support-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

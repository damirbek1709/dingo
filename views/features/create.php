<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Features $model */

$this->title = Yii::t('app', 'Create Features');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Features'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="features-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

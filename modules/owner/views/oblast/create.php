<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Create Oblast');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Oblasts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ComplaintType */

$this->title = 'Добавление';
$this->params['breadcrumbs'][] = ['label' => 'Виды жалоб', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="complaint-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

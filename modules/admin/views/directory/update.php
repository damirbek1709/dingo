<?php

/* @var $this yii\web\View */
/* @var $model app\models\Directory */

$this->title = 'Изменение: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменение';
?>
<div class="directory-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

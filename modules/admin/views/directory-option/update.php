<?php

/* @var $this yii\web\View */
/* @var $model app\models\DirectoryOption */

$this->title = 'Изменение: ' . $model->value;
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['directory/index']];
$this->params['breadcrumbs'][] = ['label' => $model->directory->title, 'url' => ['directory/view', 'id' => $model->directory->id]];
$this->params['breadcrumbs'][] = ['label' => $model->value, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменение';
?>
<div class="directory-option-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

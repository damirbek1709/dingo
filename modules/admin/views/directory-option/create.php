<?php

/* @var $this yii\web\View */
/* @var $model app\models\DirectoryOption */

$this->title = 'Добавление';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['directory/index']];
$this->params['breadcrumbs'][] = ['label' => $model->directory->title, 'url' => ['directory/view', 'id' => $model->directory->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="directory-option-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

/* @var $this yii\web\View */
/* @var $model app\models\Directory */

$this->title = 'Добавление';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="directory-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

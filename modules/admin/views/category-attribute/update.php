<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CategoryAttribute */

$this->title = 'Изменение: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['category/index']];
$this->params['breadcrumbs'][] = ['label' => $model->category->title, 'url' => ['category/view', 'id' => $model->category->id]];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменение';
?>
<div class="category-attribute-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CategoryAttribute */

$this->title = 'Добавление атрибута';
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['category/index']];
$this->params['breadcrumbs'][] = ['label' => $model->category->title, 'url' => ['category/view', 'id' => $model->category->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-attribute-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

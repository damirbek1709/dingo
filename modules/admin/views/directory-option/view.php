<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\DirectoryOption */

$this->title = $model->value;
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url' => ['directory/index']];
$this->params['breadcrumbs'][] = ['label' => $model->directory->title, 'url' => ['directory/view', 'id' => $model->directory->id]];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="directory-option-view">

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'directory_id',
                'value' => Html::a($model->directory->title, ['directory/view', 'id' => $model->directory->id]),
                'format' => 'raw',
            ],
            'value',
            'value_ky',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
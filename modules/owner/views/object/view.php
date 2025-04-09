<?php

use yii\widgets\DetailView;
use yii\helpers\Html;
use app\models\Objects;
/* @var $this yii\web\View */
/* @var $model app\models\MeilisearchModel */

$name_list = $model->name;
$title = $name_list[0];
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $title;
?>

<div class="meilisearch-view">

    <h1><?= Html::encode($title) ?></h1>
    <?php echo $this->render('top_nav', ['model' => $model]); ?>
    <p>
        <?php //= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Delete', ['delete', 'id' => $model->id], [
'class' => 'btn btn-danger',
'data' => [
'confirm' => 'Are you sure you want to delete this item?',
'method' => 'post',
],
])*/ ?>
    </p>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="header">
                        <div class="info">
                            <h2>Название</h2>
                        </div>
                        <span class="edit">✏️
                            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => '']) ?>
                        </span>

                    </div>
                    <h1 class="title"><?= $title; ?></h1>

                    <div class="info">
                        <h2>Адрес</h2>
                        <p class="address">📍 <?= $model->address[0]; ?></p>
                    </div>

                    <div class="info-grid">
                        <div>
                            <h2>Тип объекта</h2>
                            <p><?= $model->objectTypeString(); ?></p>
                        </div>
                        <div>
                            <h2>Количество номеров</h2>
                            <p>-</p>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div>
                            <h2>Заезд</h2>
                            <p><?= $model->check_in; ?></p>
                        </div>
                        <div>
                            <h2>Выезд</h2>
                            <p><?= $model->check_out ?></p>
                        </div>
                    </div>

                </div>
                <div class="view-img-list" style="margin-top:20px">
                    <?php
                    if ($bind_model->getImages()) {
                        foreach ($bind_model->getImages() as $image) {
                            echo Html::img($image->getUrl('220x150'), ['class' => 'view-thumbnail-img']);
                        }
                    }
                    ?>
                </div>

            </div>
        </div>
    </div>

</div>
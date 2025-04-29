<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */

$this->title = $model->room_title;
// $this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['view', 'id' => $object_id]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Список номеров'), 'url' => ['room-list', 'id' => $object_id]];
//$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="room-cat-view">

    <h1><?= Html::encode($this->title) ?></h1>


    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('room_nav', ['room_id' => $model->id, 'object_id' => $object_id]); ?>
            </div>
            <div class="col-md-9">

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        'room_title',
                        'guest_amount',
                        'similar_room_amount',
                        'area',
                        'bathroom',
                        'balcony',
                        'air_cond',
                        'kitchen',
                        'base_price',
                        'img',
                    ],
                ]) ?>
            </div>
        </div>
    </div>

</div>
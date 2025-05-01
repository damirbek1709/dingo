<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */

$this->title = Yii::t('app', 'Update Room Category') . " " . $model->room_title;
//$this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['object/view', 'id' => $model->id, 'object_id' => $object_id]];
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Список номеров'), 'url' => ['room-list', 'id' => $object_id]];
//$this->params['breadcrumbs'][] = ['label' => $model->room_title, 'url' => ['room', 'id' => $model->id, 'object_id' => $object_id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="oblast-update">
    <?php echo $this->render('../top_nav', ['model' => $model, 'object_id' => $object_id]); ?>
    <div class="col-md-12">

        <div class="row">
            <div class="back_link">
                <?= Html::a(Yii::t('app', 'К списку номеров'), ['room-list', 'object_id' => $object_id]) ?>
            </div>
            <div class="col-md-3">
                <?php echo $this->render('room_nav', ['room_id' => $room_id, 'object_id' => $object_id]); ?>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <?= $this->render('_form_update', [
                        'model' => $model,
                        'bindModel' => $bindModel
                    ]) ?>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .main {
        background-color: green;
        color: #fff;
    }

    .img-main:hover,
    .img-main:focus,
    .img-main.focus {
        color: #fff;
        text-decoration: none;
        outline: unset;
    }

    .object-form-container {
        max-width: 800px;
    }

    .form-group .form-label {
        display: block;
        font-size: 14px;
        font-weight: normal;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 12px 10px;
        font-size: 14px;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        background-color: #fff;
        color: #333;
        box-sizing: border-box;
        margin-bottom: 25px;
    }

    .form-input::placeholder {
        color: #c7c7c7;
    }

    .form-input:focus {
        outline: none;
        border-color: #007bff;
    }

    .help-block {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
        padding-left: 20px;
    }

    select.form-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8.825L1.175 4 2.238 2.938 6 6.7 9.763 2.938 10.825 4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 20px center;
        padding-right: 40px;
    }

    .oblast-update h1 {
        font-family: 'Inter';
        font-weight: 600;
        font-size: 20px;
        line-height: 24px;
        letter-spacing: 0px;
    }
</style>
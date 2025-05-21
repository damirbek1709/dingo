<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\BookingSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="booking-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'object_id') ?>

    <?= $form->field($model, 'room_id') ?>

    <?= $form->field($model, 'tariff_id') ?>

    <?= $form->field($model, 'sum') ?>

    <?php // echo $form->field($model, 'guest_email') ?>

    <?php // echo $form->field($model, 'guest_phone') ?>

    <?php // echo $form->field($model, 'guest_name') ?>

    <?php // echo $form->field($model, 'date_from') ?>

    <?php // echo $form->field($model, 'date_to') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'other_guests') ?>

    <?php // echo $form->field($model, 'cancellation_type') ?>

    <?php // echo $form->field($model, 'cancellation_penalty_sum') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'special_comment') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Booking $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="booking-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'object_id')->textInput() ?>

    <?= $form->field($model, 'room_id')->textInput() ?>

    <?= $form->field($model, 'tariff_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sum')->textInput() ?>

    <?= $form->field($model, 'guest_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'guest_phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'guest_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_from')->textInput() ?>

    <?= $form->field($model, 'date_to')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'other_guests')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cancellation_type')->textInput() ?>

    <?= $form->field($model, 'cancellation_penalty_sum')->textInput() ?>

    <?= $form->field($model, 'special_comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

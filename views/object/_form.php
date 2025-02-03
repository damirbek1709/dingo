<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="oblast-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>
    <?php //= $form->field($model, 'features')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'site')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'check_in')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'check_out')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'reception')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
    <?= $form->field($model, 'lat')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'lon')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
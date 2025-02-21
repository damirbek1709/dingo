<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\models\ComplaintType;

/* @var $this yii\web\View */
/* @var $model app\models\ComplaintType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="complaint-type-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->widget(Select2::className(), [
        'data' => ComplaintType::getTypeOptions(),
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

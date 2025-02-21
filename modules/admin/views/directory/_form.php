<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Directory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="directory-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?php //echo $form->field($model, 'title_ky')->textInput(['maxlength' => true]); 
    ?>

    <?= $form->field($model, 'prompt')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'prompt_ky')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Vocabulary;

/** @var yii\web\View $this */
/** @var app\models\Vocabulary $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vocabulary-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_en')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_ky')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'model')->dropDownList(Vocabulary::list(Vocabulary::MODEL_TYPE_MEAL)) ?>

    <div class="form-group"> 
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

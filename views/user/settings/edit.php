<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="oblast-update">

    <div class="row">
        <div class="col-md-5">
            <div class="card">
            <h2 class="general_title"><?php echo Yii::t('app', 'Редактировать') ?></h2>
                <?php $form = ActiveForm::begin([
                    'enableClientValidation' => true,
                    'options' => [
                        'enctype' => 'multipart/form-data'
                    ]
                ]); ?>

                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-input']) ?>
                <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'class' => 'form-input']) ?>
                <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'class' => 'form-input']) ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
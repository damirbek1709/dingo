<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\CategoryAttribute;
use dosamigos\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model app\models\CategoryAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-attribute-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->hiddenInput(['value' => CategoryAttribute::TYPE_INTEGER])->label(false) ?>

    <?= $form->field($model, 'category_id')->hiddenInput(['value' => $model->category_id])->label(false) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'title_ky')->textInput(['maxlength' => true]) ?>

    <?php /* echo $form->field($model, 'description')->widget(CKEditor::className(), [
        'options' => [
            'id' => rand(),
        ],
        'preset' => 'standard'
    ]); */ ?>
    <?php /* echo $form->field($model, 'description_ky')->widget(CKEditor::className(), [
        'options' => [
            'id' => rand(),
        ],
        'preset' => 'standard'
    ]);*/ ?>

    <?= $form->field($model, 'description')->textInput() ?>
    <?= $form->field($model, 'description_ky')->textInput() ?>

    <?= $form->field($model, 'is_required')->checkbox() ?>

    <?= $form->field($model, 'is_searchable')->checkbox() ?>

    <?= $form->field($model, 'is_searchable_price')->checkbox() ?>

    <?= $form->field($model, 'minimum')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'maximum')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
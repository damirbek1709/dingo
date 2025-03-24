<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="tariff-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>


    <div class="tariff_payment_block">
        <h3><?= Yii::t('app', 'Модель оплаты'); ?></h3>
        <?= $form->field($model, 'payment_on_book')->checkbox() ?>
        <?= $form->field($model, 'payment_on_reception')->checkbox() ?>
    </div>

    <div class="tariff_cancellation_block">
        <h3><?= Yii::t('app', 'Отмена и штрафы'); ?></h3>
        <?= $form->field($model, 'cancellation')->radioList([1, 2, 3]) ?>
    </div>

    <div class="tariff_meal_block">
        <h3><?= Yii::t('app', 'Питание'); ?></h3>
        <?= $form->field($model, 'meal_type')->dropDownList([1, 2, 3, 4]) ?>
    </div>
    
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
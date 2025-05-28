<?php

use app\models\RoomCat;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="col-md-6">

    <div class="oblast_update">
        <?php $form = ActiveForm::begin([
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
            'options' => [
                'enctype' => 'multipart/form-data',
                'class' => 'styled-form'
            ],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-input'],
                'errorOptions' => ['class' => 'help-block'],
                'options' => ['class' => 'form-group'],
            ],
        ]); ?>

        <?= $form->field($model, 'type_id')->dropDownList($model->typeList(), ['disabled' => true])->label(Yii::t('app', 'Тип номера')) ?>


        <div class="form-group">
            <label class="form-label"
                for="guest_amount_id"><?= Yii::t('app', 'Максимальное количество гостей') ?></label>
            <div class="increment-input" style="margin-top: 0;">
                <?= Html::input('text', 'RoomCat[guest_amount]', $model->guest_amount ? $model->guest_amount : 1, [
                    'class' => 'form-control children-count',
                    'readonly' => true,
                    'disabled'=>true,
                    'label' => 'Количество гостей',
                    //'name'=>'RoomCat[guest_amount]'
                ]); ?>
                
            </div>
        </div>

       
        <div id="default-prices-wrapper" style="display: grid; grid-template-columns: 1fr 1fr 1fr; grid-gap: 0 20px;">
            <?php
            $defaultPrices = is_array($model->default_prices) ? $model->default_prices : [];
            $count = $model->guest_amount ?: 1;
            for ($i = 0; $i < $count; $i++): ?>
                <div class="form-group default-price-input">
                    <?= Html::label("Цена за " . ($i + 1) . " гостя", "RoomCat_default_prices_$i", ['class' => 'form-label']) ?>
                    <?= Html::textInput("RoomCat[default_prices][$i]", $defaultPrices[$i] ?? '', [
                        'class' => 'form-input',
                        'id' => "RoomCat_default_prices_$i",
                        'required' => true,
                        'disabled'=>true,
                        'oninvalid' => "this.setCustomValidity('Пожалуйста, укажите цену')",
                        'oninput' => "this.setCustomValidity('')"
                    ]) ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="clear"></div>

        <?php //= $form->field($model, 'guest_amount')->textInput() ?>
        <div class="terms_section">
            <?= $form->field($model, 'similar_room_amount')->textInput(['disabled'=>true,]) ?>
        </div>


        <?= $form->field($model, 'area')->textInput() ?>
        <div class="form-group" style="margin-bottom:25px">
            <div class="checkbox-group">
                <?= $form->field($model, 'bathroom')->checkbox(['class' => 'checkbox-input','disabled'=>true,]) ?>
                <?= $form->field($model, 'balcony')->checkbox(['class' => 'checkbox-input','disabled'=>true,]) ?>
                <?= $form->field($model, 'air_cond')->checkbox(['class' => 'checkbox-input','disabled'=>true,]) ?>
                <?= $form->field($model, 'kitchen')->checkbox(['class' => 'checkbox-input','disabled'=>true,]) ?>
            </div>
        </div>


        <?php echo $form->errorSummary($model); ?>


        <?php // Get all images ?>

        <?php
        if (Yii::$app->controller->action->id == 'edit-room') {
            $images = $bindModel->getImages();
            //$initial_preview = $bindModel->imagesPreview();
        } else {
            $images = $model->getImages();
        }
        ?>

        <?php ActiveForm::end(); ?>

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

    .select-container {
        position: relative;
        margin-bottom: 20px;
    }

    .select-container::after {
        content: "";
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #888;
        pointer-events: none;
    }

    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-color: white;
        color: #888;
    }

    .quantity-btn {
        background: none;
        border: none;
        width: 40px;
        height: 40px;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #888;
    }

    .quantity-display {
        width: 60px;
        text-align: center;
        font-size: 16px;
        border: none;
        background: none;
    }

    /* Checkbox styles */


    /* Space between sections */
    .form-section {
        margin-bottom: 25px;
    }

    /* Heading styles */
    .section-heading {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 15px;
    }

    /* Grid headings */
    .grid-heading {
        font-weight: 500;
        margin-bottom: 15px;
    }
</style>
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

        <?= $form->field($model, 'type_id')->dropDownList(items: $model->typeList())->label(Yii::t('app', 'Тип номера')) ?>


        <div class="form-group">
            <label class="form-label"
                for="guest_amount_id"><?= Yii::t('app', 'Максимальное количество гостей') ?></label>
            <div class="increment-input" style="margin-top: 0;">
                <button type="button" class="decrement decrease">-</button>
                <?= Html::input('text', 'RoomCat[guest_amount]', $model->guest_amount ? $model->guest_amount : 1, [
                    'class' => 'form-control children-count',
                    'readonly' => true,
                    'label' => 'Количество гостей',
                    //'name'=>'RoomCat[guest_amount]'
                ]); ?>
                <button type="button" class="increment increase">+</button>
            </div>
        </div>

        <div class="clear"></div>

        <?php //= $form->field($model, 'guest_amount')->textInput() ?>
        <div class="terms_section">
            <?= $form->field($model, 'similar_room_amount')->textInput() ?>
        </div>

        
        <?= $form->field($model, 'area')->textInput() ?>
        <div class="form-group" style="margin-bottom:25px">
            <div class="checkbox-group">
                <?= $form->field($model, 'bathroom')->checkbox(['class' => 'checkbox-input']) ?>
                <?= $form->field($model, 'balcony')->checkbox(['class' => 'checkbox-input']) ?>
                <?= $form->field($model, 'air_cond')->checkbox(['class' => 'checkbox-input']) ?>
                <?= $form->field($model, 'kitchen')->checkbox(['class' => 'checkbox-input']) ?>
            </div>
        </div>

        <?= $form->field($model, 'base_price')->textInput() ?>
        <?php echo $form->field($model, 'img')->hiddenInput()->label(false); ?>
        <?php echo $form->errorSummary($model); ?>


        <?php // Get all images ?>

        <?php
        if (Yii::$app->controller->action->id == 'edit-room') {
            $images = $bindModel->getImages();
            //$initial_preview = $bindModel->imagesPreview();
        } else {
            $images = $model->getImages();
        }


        // echo $form->field($model, 'images[]')->widget(
        //     \kartik\file\FileInput::class,
        //     [
        //         'options' => ['accept' => 'image/*', 'multiple' => 'true'],
        //         'pluginOptions' => [
        //                 'previewFileType' => 'image',
        //                 'initialPreview' => $initial_preview,
        //                 'initialPreviewConfig' => array_map(function ($image) use ($model) {
        //                     return [
        //                         'url' => Url::to(['object/remove-room-image', 'image_id' => $image->id, 'model' => 'RoomCat', 'model_id' => $model->id]),
        //                         'key' => $image->id,
        //                         'extra' => [
        //                                 'main' => $image->id,
        //                             ],
        //                     ];
        //                 }, $images),
        //                 'overwriteInitial' => false,
        //                 'initialPreviewAsData' => true,
        //                 'initialPreviewFileType' => 'image',
        //                 'initialPreviewShowDelete' => true,
        //                 'showRemove' => false,
        //                 'showUpload' => false,
        //                 'otherActionButtons' => '
        //                             <button type="button" class="kv-cust-btn img-main btn btn-sm" title="Set as main">
        //                                 <i class="glyphicon glyphicon-ok"></i>
        //                             </button>
        //                             ',
        //                 'fileActionSettings' => [
        //                     'showZoom' => false,
        //                 ],
        //             ],
        //     ]
        // ); ?>



        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<script>

    document.addEventListener('DOMContentLoaded', function () {
        // Get all increase/decrease buttons
        const decreaseButtons = document.querySelectorAll('.decrease');
        const increaseButtons = document.querySelectorAll('.increase');

        // Add event listeners to decrease buttons
        decreaseButtons.forEach(button => {
            button.addEventListener('click', function () {
                const input = $('.children-count');
                let value = parseInt(input.val());
                if (value > 0) {
                    //input.val = value - 1;
                    input.attr('value', value - 1);
                }
            });
        });

        // Add event listeners to increase buttons
        increaseButtons.forEach(button => {
            button.addEventListener('click', function () {
                const input = $('.children-count');
                let value = parseInt(input.val());
                //input.value = value + 1;
                input.attr('value', value + 1);
            });
        });
    });

    document.querySelectorAll('.checkbox-group input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const id = this.dataset.id;
            const quantityInput = document.querySelector(`#quantity-${id}`);

            // Enable or disable the quantity input based on checkbox state
            if (this.checked) {
                quantityInput.removeAttribute('readonly');
            } else {
                quantityInput.setAttribute('readonly', 'true');
                quantityInput.value = 0; // Reset quantity to 0 when unchecked
            }
        });
    });

    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const quantityInput = document.querySelector(`#quantity-${id}`);
            let currentValue = parseInt(quantityInput.value) || 0;

            if (this.classList.contains('increase')) {
                currentValue++;
            } else if (this.classList.contains('decrease') && currentValue > 0) {
                currentValue--;
            }

            quantityInput.value = currentValue; // Update the quantity field
        });
    });




    // Количество кнопки +/-


</script>

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
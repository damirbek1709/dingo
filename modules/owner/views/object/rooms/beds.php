<?php

use app\models\RoomCat;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="oblast-update">

    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="col-md-12">
        <div class="back_link">
            <?= Html::a(Yii::t('app', 'К списку номеров'), ['room-list', 'object_id' => $object_id]) ?>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('room_nav', ['room_id' => $room_id, 'object_id' => $object_id]); ?>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="form-section form-group">
                        <div class="bed-type-row bed-types-grid">
                            <div>
                                <label class="form-label"><?= Yii::t('app', 'Тип кровати') ?>
                                    <span class="required_star">*</span>
                                </label>
                            </div>

                            <div>
                                <label class="form-label"><?= Yii::t('app', 'Количество') ?>
                                    <span class="required_star">*</span>
                                </label>
                            </div>


                            <?php foreach ($model->bedTypes() as $id => [$label, $info]): ?>
                                <div class="checkbox-group checkbox-grid">
                                    <!-- Checkbox for bed type selection -->
                                    <?= $form->field($model, "bed_types[{$id}][checked]")->checkbox([
                                        'label' => false,
                                        'value' => 1,
                                        'uncheck' => 0,
                                        'data-id' => $id,
                                    ])->label(false) ?>
                                    <div>
                                        <?= $label ?>
                                        <div class="bed-info"><?= $info ?></div>
                                    </div>
                                </div>

                                <div class="quantity-input">
                                    <button type="button" class="quantity-btn decrease" data-id="<?= $id ?>">−</button>

                                    <?= $form->field($model, "bed_types[{$id}][quantity]")->input('text', [
                                        'min' => 0,
                                        'value' => isset($model->bed_types[$id]) ? $model->bed_types[$id]['quantity'] : 0, // Prepopulate the quantity with saved value
                                        'readonly' => !isset($model->bed_types[$id]) || $model->bed_types[$id]['quantity'] == 0, // If quantity is 0 or not set, make it readonly
                                        'class' => 'quantity-display',
                                        'id' => "quantity-{$id}", // Unique ID for each input
                                    ])->label(false) ?>

                                    <button type="button" class="quantity-btn increase" data-id="<?= $id ?>">+</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>

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
                const input = this.nextElementSibling;
                let value = parseInt(input.value);
                if (value > 0) {
                    input.value = value - 1;
                }
            });
        });

        // Add event listeners to increase buttons
        increaseButtons.forEach(button => {
            button.addEventListener('click', function () {
                const input = this.previousElementSibling;
                let value = parseInt(input.value);
                input.value = value + 1;
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
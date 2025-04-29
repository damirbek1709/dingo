<?php

use app\models\Tariff;
use app\modules\owner\controllers\ObjectController;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="tariff-form">
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

    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label(Yii::t('app', 'Название тарифа')); ?>

    <div class="tariff_payment_block">
        <h3><?= Yii::t('app', 'Модель оплаты'); ?></h3>
        <?= $form->field($model, 'payment_on_book')->checkbox(); ?>
        <?php //$form->field($model, 'payment_on_reception')->checkbox() ?>
        <?= $form->field($model, 'room_list[]')->hiddenInput()->label(false); ?>
    </div>

    <div class="tariff_cancellation_block">
        <h3><?= Yii::t('app', 'Отмена и штрафы'); ?></h3>
        <?= $form->field($model, 'cancellation')->radioList(
            array_map(function ($item) {
                    return $item['label'] . "<br><small style='color: gray;font-weight:normal'>" . $item['hint'] . "</small>";
                }, $model->getCancellationList()),
            [
                'encode' => false, // Allow HTML rendering for hints
                'itemOptions' => ['class' => 'cancellation-option'], // Custom class for styling
            ]
        )->label(false); ?>
        <div class="penalty_block">
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <?= $form->field($model, 'penalty_sum')->textInput() ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'penalty_days')->textInput() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tariff_meal_block clear">
        <h3><?= Yii::t('app', 'Питание'); ?></h3>
        <?= $form->field($model, 'meal_type')->dropDownList(
            array_map(function ($item) {
                return $item['label'];
            }, $model->getMealList()),
            [
                'encode' => false, // Allow HTML rendering for hints
                'itemOptions' => ['class' => 'cancellation-option'], // Custom class for styling
            ]
        )->label(Yii::t('app', 'Включено в стоимость тарифа, указать стоимость можно во вкладке “Отель”, раздел “Условия”')); ?>
    </div>

    <div class="tariff_meal_block">
        <h3><?= Yii::t('app', 'Привязать номер'); ?></h3>
        <?php
        $room_list = $model->getRoomList($object_id);
        foreach ($room_list as $room): ?>
            <label>
                <input type="checkbox" name="Tariff[room_list][]" value="<?php echo $room['id'] ?>"
                    object_id="<?php echo $object_id ?>" tariff_id="<?= $model->id ?>" room_id="<?php echo $room['id'] ?>"
                    class="room-bind">
                <?php echo $room['room_title']; ?>
            </label>
        <?php endforeach; ?>
    </div>



    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    $(document).ready(function () {
        let selectedRooms = [];

        $('.tariff-room-cover').on('click', function () {
            let roomId = parseInt($(this).attr('room_id'));

            if ($(this).hasClass('room-selected')) {
                $(this).removeClass('room-selected');
                selectedRooms = selectedRooms.filter(id => id !== roomId);
            } else {
                $(this).addClass('room-selected');
                selectedRooms.push(roomId);
            }

            $('#tariff-room_list').val(JSON.stringify(selectedRooms));
        });

        function togglePenaltyFields(selectedValue) {

            // Check if the container for penalty fields already exists, if not create it
            let penaltyFieldsContainer = $('.penalty-fields-container');
            if (penaltyFieldsContainer.length === 0) {
                // Add the container after the cancellation block
                $('.tariff_cancellation_block').after('<div class="penalty-fields-container"></div>');
                penaltyFieldsContainer = $('.penalty-fields-container');
            }

            var penalty_option = "<?php echo Tariff::FREE_CANCELLATION_WITH_PENALTY; ?>";

            // Show or hide penalty fields based on selection
            if (selectedValue === penalty_option) {
                // If we need to show the fields, populate the container
                $('.penalty_block').css('display', 'block');
                $('#tariffform-penalty_sum').val('');
                $('#tariffform-days').val('');
            } else {
                $('.penalty_block').css('display', 'none');
                // Hide the fields if another option is selected
                penaltyFieldsContainer.hide();
            }
        }

        // Run the function once on page load to set the initial state

        $(document).on('change', '.cancellation-option', function () {
            var selectedValue = $(this).val();
            togglePenaltyFields(selectedValue);
        });


    });
</script>

<style>
    .room-selected {
        background-color: #f6f6f6;
        padding: 15px;
    }

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

    .penalty_block {
        display: none;
    }

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

    .object-form-container {
        max-width: 800px;
    }

    .form-group .form-label {
        display: block;
        font-size: 14px;
        font-weight: normal;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 12px 10px;
        font-size: 14px;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        background-color: #fff;
        color: #333;
        box-sizing: border-box;
        margin-bottom: 25px;
    }

    .form-input::placeholder {
        color: #c7c7c7;
    }

    .form-input:focus {
        outline: none;
        border-color: #007bff;
    }

    .help-block {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
        padding-left: 20px;
    }

    select.form-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8.825L1.175 4 2.238 2.938 6 6.7 9.763 2.938 10.825 4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 20px center;
        padding-right: 40px;
    }

    .oblast-update h1 {
        font-family: 'Inter';
        font-weight: 600;
        font-size: 20px;
        line-height: 24px;
        letter-spacing: 0px;
    }
</style>
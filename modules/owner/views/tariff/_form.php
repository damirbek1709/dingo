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

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>


    <div class="tariff_payment_block">
        <h3><?= Yii::t('app', 'Модель оплаты'); ?></h3>
        <?= $form->field($model, 'payment_on_book')->checkbox() ?>
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
    </div>

    <div class="tariff_meal_block">
        <h3><?= Yii::t('app', 'Питание'); ?></h3>
        <?= $form->field($model, 'meal_type')->dropDownList([1 => 'Завтрак', 2 => 'Трехразовое питание'])->label('Включено в цену тарифа, указать стоимость можно во вкладке отель'); ?>
    </div>

    <div class="tariff_meal_block">
        <h3><?= Yii::t('app', 'Привязать номер'); ?></h3>
        <?php
        $room_list = $model->getRoomList($object_id);
        ?>
        <div class="tariff-room-list">
            <?php foreach ($room_list as $room) {
                echo Html::beginTag('div', ['class' => 'tariff-room-cover', 'room_id' => $room['id']]);
                echo Html::tag('div', $room['room_title'], ['class' => 'tariff-room-title']);
                echo Html::img($room['images'][0]['thumbnailPicture']);
                echo Html::endTag('div');
            } ?>
        </div>
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

        function togglePenaltyFields() {
            // Get the currently selected cancellation option value
            const selectedValue = $('input[name="TariffForm[cancellation]"]:checked').val();

            // Check if the container for penalty fields already exists, if not create it
            let penaltyFieldsContainer = $('.penalty-fields-container');
            if (penaltyFieldsContainer.length === 0) {
                // Add the container after the cancellation block
                $('.tariff_cancellation_block').after('<div class="penalty-fields-container"></div>');
                penaltyFieldsContainer = $('.penalty-fields-container');
            }

            // Show or hide penalty fields based on selection
            if (selectedValue === "<?php echo Tariff::FREE_CANCELLATION_WITH_PENALTY;?>") {
                // If we need to show the fields, populate the container
                penaltyFieldsContainer.html(`
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group field-tariffform-penalty_sum">
                            <label class="control-label" for="tariffform-penalty_sum">Сумма штрафа</label>
                            <input type="text" id="tariffform-penalty_sum" class="form-control" name="TariffForm[penalty_sum]">
                            <div class="help-block"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group field-tariffform-days">
                            <label class="control-label" for="tariffform-days">Количество дней</label>
                            <input type="number" id="tariffform-days" class="form-control" name="TariffForm[days]" min="1">
                            <div class="help-block"></div>
                        </div>
                    </div>
                </div>
            `);
                penaltyFieldsContainer.show();
            } else {
                // Hide the fields if another option is selected
                penaltyFieldsContainer.hide();
            }
        }

        // Run the function once on page load to set the initial state
        togglePenaltyFields();


    });
</script>
<style>
    .room-selected {
        background-color: #f6f6f6;
        padding: 15px;
    }
</style>
<?php

use app\models\Tariff;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\models\Objects;

/** @var yii\web\View $this */
/** @var app\models\TariffSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */



// $this->title = Yii::t('app', 'Tariffs');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">
    <!-- <h1><?php //echo Html::encode($object_title) ?></h1> -->
    <?php echo $this->render('../../views/object/top_nav', ['model' => $model, 'object_id' => $object_id]); ?>

    <div class="card">
        <div>
            <div style="float:left">
                <div class="button-container">
                    <button
                        class="button button-secondary "><?= Html::a(Yii::t('app', 'Номера'), ['room-list', 'object_id' => $model->id]) ?></button>
                    <button
                        class="button button-primary active"><?= Html::a(Yii::t('app', 'Тарифы'), ['tariff-list', 'object_id' => $model->id]) ?></button>
                </div>
            </div>

            <div style="float:right">
                <?= Html::a(Yii::t('app', '+ Добавить тариф'), '#', ['class' => 'add-room-btn modal-button']) ?>
            </div>
        </div>

        <?php
        $room_list_session = [];
        $sessionKey = 'updated_rooms_' . $object_id;
        if (Yii::$app->session->has($sessionKey)) {
            $updatedRooms = Yii::$app->session->get($sessionKey);
            Yii::$app->session->remove($sessionKey);
            $room_list_session = $updatedRooms;
        }
        echo ListView::widget([
            'options' => [
                'class' => 'product-index-cover',
            ],
            'dataProvider' => $dataProvider,
            'viewParams' => ['object_id' => $model->id, 'room_list_session' => $room_list_session],
            'itemView' => '_item',
            'summary' => false,
            'itemOptions' => [
                'class' => 'best-seller-block',
            ],
        ]); ?>
    </div>
</div>

<?php

$tariff = new Tariff();
Modal::begin([
    'id' => 'add-tariff-modal',
    'size' => 'modal-md',
    'header' => "<h1 class='dialog-title'>" . Yii::t('app', 'Создать тариф') . "</h1>",
    'options' => ['class' => 'status-gallery-modal'],
]); ?>

<div class="tariff-form">
    <?php $form = ActiveForm::begin([
        'action' => ['/owner/object/add-tariff', 'object_id' => $model->id],
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

    <?= $form->field($tariff, 'title')->textInput(['maxlength' => true])->label(Yii::t('app', 'Название тарифа')); ?>
    <?= $form->field($tariff, 'title_en')->textInput(['maxlength' => true])->label(Yii::t('app', 'Название тарифа на английском')); ?>
    <?= $form->field($tariff, 'title_ky')->textInput(['maxlength' => true])->label(Yii::t('app', 'Название тарифа на кыргызском')); ?>

    <div class="tariff_payment_block">
        <h3><?= Yii::t('app', 'Модель оплаты'); ?></h3>
        <?= $form->field($tariff, 'payment_on_book')->checkbox(); ?>
        <?php //$form->field($model, 'payment_on_reception')->checkbox() ?>
        <?= $form->field($tariff, 'room_list[]')->hiddenInput()->label(false); ?>
    </div>

    <div class="tariff_cancellation_block">
        <h3><?= Yii::t('app', 'Отмена и штрафы'); ?></h3>
        <?= $form->field($tariff, 'cancellation')->radioList(
            array_map(function ($item) {
                    return $item['label'] . "<br><small style='color: gray;font-weight:normal'>" . $item['hint'] . "</small>";
                }, $tariff->getCancellationList()),
            [
                'encode' => false, // Allow HTML rendering for hints
                'itemOptions' => ['class' => 'cancellation-option'], // Custom class for styling
            ]
        )->label(false); ?>


        <?php
        $visible = "none";
        if ($tariff->cancellation && $tariff->cancellation == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
            $visible = "block";
        }

        ?>
        <div class="penalty_block" style="display:<?= $visible ?>">
            <div class="row">
                <div class="">

                    <div class="col-md-6">
                        <?= $form->field($tariff, 'penalty_days')->textInput() ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($tariff, 'penalty_sum')->textInput() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tariff_meal_block clear">
        <h3><?= Yii::t('app', 'Питание'); ?></h3>
        <?= $form->field($tariff, 'meal_type')->dropDownList(Objects::mealList())->label(Yii::t('app', 'Включено в стоимость тарифа, указать стоимость можно во вкладке “Отель”, раздел “Условия”'));
        ; ?>
        <?php
        // echo $form->field($model, 'meal_type')->dropDownList(
        //     array_map(function ($item) {
        //         return $item['label'];
        //     }, $model->getMealList()),
        //     [
        //         'encode' => false, // Allow HTML rendering for hints
        //         'itemOptions' => ['class' => 'cancellation-option'], // Custom class for styling
        //     ]
        // )->label(Yii::t('app', 'Включено в стоимость тарифа, указать стоимость можно во вкладке “Отель”, раздел “Условия”')); 
        // ?>
    </div>

    <div class="tariff_meal_block">
        <h3><?= Yii::t('app', 'Привязать номер'); ?></h3>
        <?php
        $room_list = $tariff->getRoomList($model->id);
        $room_list_arr = [];
        foreach ($room_list as $room) {
            $room_list_arr[$room['id']] = $room['room_title'][0];
        }
        echo $form->field($tariff, 'room_list')->checkboxList($room_list_arr)->label(false);
        ?>
    </div>




    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php Modal::end();
?>

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

    $(document).on('click', '.modal-button', function () {
        $('#add-tariff-modal').modal('show');
    });
    $(document).ready(function () {
        // When any of the options button is clicked, toggle its corresponding menu
        $('.room-card-options .options-btn').on('click', function (e) {
            e.stopPropagation(); // Prevent click event from bubbling up
            const $menu = $(this).siblings('.options-menu');

            // Close all other open menus
            $('.room-card-options .options-menu').not($menu).slideUp();

            // Toggle the clicked menu
            $menu.stop(true, true).slideToggle();
        });

        // Close all menus if the user clicks anywhere outside the options menu
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.room-card-options').length) {
                $('.room-card-options .options-menu').slideUp();
            }
        });

        $('.dropdown-btn').on('click', function () {
            $(this).parent().toggleClass('active');
        });

        // Close the dropdown if clicking outside of it
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.tariff-dropdown').length) {
                $('.tariff-dropdown').removeClass('active');
            }
        });


        $('.room-bind').on('change', function () {
            var tariffId = $(this).attr('tariff_id');  // Get the tariff ID
            var room_id = $(this).attr('room_id');
            var isChecked = $(this).prop('checked');  // Check if the checkbox is checked
            var object_id = $(this).attr('object_id');  // Check if the checkbox is checked

            // Send AJAX request to the backend
            $.ajax({
                url: "<?= Yii::$app->urlManager->createUrl('/owner/tariff/bind-room') ?>", // Your action URL
                type: 'POST',
                data: {
                    tariff_id: tariffId,  // Send the tariff ID
                    checked: isChecked,
                    room_id: room_id,   // Send the checked state
                    object_id: object_id,   // Send the checked state
                    _csrf: $('meta[name="csrf-token"]').attr('content')  // CSRF token for security (if needed)
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Tariff successfully updated');
                    } else {
                        console.log('Failed to update tariff');
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX error: ' + status + ' ' + error);
                }
            });
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
        visibility: visible;
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

    .dialog-title {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }

    .info-text {
        font-size: 16px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }

    li {
        margin-bottom: 8px;
        font-size: 16px;
    }

    .modal-header {
        padding: 25px 45px 0;
        border-bottom: 0;
    }
</style>
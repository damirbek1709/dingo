<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="object-form-container">
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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите название объекта')]) ?>
    <?= $form->field($model, 'name_en')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите название объекта английском')]) ?>
    <?= $form->field($model, 'name_ky')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите название объекта кыргызском')]) ?>

    <?= $form->field($model, 'type')->dropDownList(
        $model->objectTypeList(),
        [
            'prompt' => Yii::t('app', 'Укажите тип объекта'),
            'class' => 'form-input'
        ]
    ) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите город')]) ?>
    <?= $form->field($model, 'city_en')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите город английском')]) ?>
    <?= $form->field($model, 'city_ky')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите город кыргызском')]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес')]) ?>
    <?= $form->field($model, 'address_en')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес английском')]) ?>
    <?= $form->field($model, 'address_ky')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес кыргызском')]) ?>

</div>
<?php
$initial_preview = false;
if ($model->isImageSet()) {
    $initial_preview = $model->imagesPreview();
}
$images = $model->getImages(); // Get all images
echo $form->field($model, 'images[]')->widget(
    \kartik\file\FileInput::class,
    [
        'options' => ['accept' => 'image/*', 'multiple' => 'true'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'initialPreview' => $initial_preview,
            'initialPreviewConfig' => array_map(function ($image) use ($model) {
                return [
                    // 'url' => Url::to(['/object/remove-image', 'image_id' => $image->id, 'object_id' => $model->id]),
                    // 'key' => $image->id,
                    // 'extra' => [
                    //     'main' => $image->id,
                    // ],
                ];
            }, $images),
            'overwriteInitial' => false,
            'initialPreviewAsData' => true,
            'initialPreviewFileType' => 'image',
            'initialPreviewShowDelete' => true,
            'showRemove' => false,
            'showUpload' => false,
            'otherActionButtons' => '
                                <button type="button" class="kv-cust-btn img-main btn btn-sm" title="Set as main">
                                    <i class="glyphicon glyphicon-ok"></i>
                                </button>
                                ',
            'fileActionSettings' => [
                'showZoom' => false,
            ],
        ],
    ]
)->label(Yii::t('app', 'Фотографии')); ?>


<div class="object-form-container">
    <?php //= $form->field($model, 'features')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Номер телефона')]) ?>
    <?= $form->field($model, 'site')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Сайт')]) ?>
    <?= $form->field($model, 'check_in')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Заезд')]) ?>
    <?= $form->field($model, 'check_out')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Выезд')]) ?>
    <?= $form->field($model, 'general_room_count')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Общее количество комнат')]) ?>
    <?= $form->field($model, 'reception')->checkbox() ?>

    <?= $form->field($model, 'description')->widget(
        Widget::className(),
        [
            'settings' => [
                'lang' => 'ru',
                'minHeight' => 200,
                'formatting' => ['p', 'blockquote', 'h2'],
                'imageCaption' => true,
                'imageUpload' => Url::to(['site/image-upload']),
                'fileUpload' => Url::to(['site/file-upload']),
                'plugins' => [
                    'imagemanager',
                    'filemanager',
                    'clips',
                    'fullscreen',
                    'table',
                    'fontsize',
                    'fontcolor',
                    'video',
                ]
            ],
        ]
    ); ?>

    <?= $form->field($model, 'description_en')->widget(
        Widget::className(),
        [
            'settings' => [
                'lang' => 'ru',
                'minHeight' => 200,
                'formatting' => ['p', 'blockquote', 'h2'],
                'imageCaption' => true,
                'imageUpload' => Url::to(['site/image-upload']),
                'fileUpload' => Url::to(['site/file-upload']),
                'plugins' => [
                    'imagemanager',
                    'filemanager',
                    'clips',
                    'fullscreen',
                    'table',
                    'fontsize',
                    'fontcolor',
                    'video',
                ]
            ],
        ]
    ); ?>

    <?= $form->field($model, 'description_ky')->widget(
        Widget::className(),
        [
            'settings' => [
                'lang' => 'ru',
                'minHeight' => 200,
                'formatting' => ['p', 'blockquote', 'h2'],
                'imageCaption' => true,
                'imageUpload' => Url::to(['site/image-upload']),
                'fileUpload' => Url::to(['site/file-upload']),
                'plugins' => [
                    'imagemanager',
                    'filemanager',
                    'clips',
                    'fullscreen',
                    'table',
                    'fontsize',
                    'fontcolor',
                    'video',
                ]
            ],
        ]
    ); ?>

    <div id="map" style="width: 800px; height: 400px;"></div>
    <?php
    if ($model->lat)
        $lat = $model->lat;
    else
        $lat = 41.2044;
    if ($model->lon)
        $lon = $model->lon;
    else
        $lon = 74.7661;
    ?>
    <?= $form->field($model, 'lat')->hiddenInput(['maxlength' => true, 'value' => $lat])->label(false) ?>
    <?= $form->field($model, 'lon')->hiddenInput(['maxlength' => true, 'value' => $lon])->label(false); ?>
    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'E-mail')]) ?>
    <?php echo $form->field($model, 'img')->hiddenInput()->label(false); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    ymaps.ready(init);

    function init() {
        const map = new ymaps.Map('map', {
            center: [41.2044, 74.7661], // Moscow coordinates as default
            zoom: 10
        });

        // Create a draggable placemark
        const placemark = new ymaps.Placemark(
            [41.2044, 74.7661],
            {},
            { draggable: true }
        );

        map.geoObjects.add(placemark);

        // Update coordinates on drag
        placemark.events.add('dragend', function () {
            const coords = placemark.geometry.getCoordinates();
            $('#latitude').val(coords[0]);
            $('#longitude').val(coords[1]);
        });

        // Update coordinates initially
        $('#latitude').val(placemark.geometry.getCoordinates()[0]);
        $('#longitude').val(placemark.geometry.getCoordinates()[1]);
    }

    var mainImgIdField = $('#object-img');
    $('body').on('click', '.img-main', function () {
        var imgId = $(this).siblings('.kv-file-remove').attr('data-key');
        mainImgIdField.val(imgId);
        $('.file-preview-thumbnails .file-preview-frame').removeClass('main');
        $('.img-main').removeClass('main');
        $(this).addClass('main');
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

    .form-group {
        margin-bottom: 5px;
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
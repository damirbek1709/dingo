<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use dosamigos\fileupload\FileUploadUI;
use kartik\file\FileInput;


/** @var yii\web\View $this */
/** @var app\models\Oblast $model */
/** @var yii\widgets\ActiveForm $form */
?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<div class="oblast-form">

    <?php $form = ActiveForm::begin([
        //'enableClientValidation' => true,
        //'enableAjaxValidation' => true,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]);
    $name_list = $model->name;
    $city_list = $model->city;
    $address_list = $model->address;
    $description_list = $model->description;

    $csrfToken = Yii::$app->request->csrfToken;
    $csrfParam = Yii::$app->request->csrfParam;

    $name = $name_list[0] ? $name_list[0] : "";
    $name_en = $name_list[1] ? $name_list[1] : "";
    $name_ky = $name_list[2] ? $name_list[2] : "";

    $city = $city_list[0] ? $city_list[0] : "";
    $city_en = $city_list[1] ? $city_list[1] : "";
    $city_ky = $city_list[2] ? $city_list[2] : "";

    $address = $address_list[0] ? $address_list[0] : "";
    $address_en = $address_list[1] ? $address_list[1] : "";
    $address_ky = $address_list[2] ? $address_list[2] : "";

    $description = $description_list[0] ? $description_list[0] : "";
    $description_en = $description_list[1] ? $description_list[1] : "";
    $description_ky = $description_list[2] ? $description_list[2] : "";
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'value' => $name]) ?>
    <?= $form->field($model, 'name_en')->textInput(['maxlength' => true, 'value' => $name_en]) ?>
    <?= $form->field($model, 'name_ky')->textInput(['maxlength' => true, 'value' => $name_ky]) ?>
    <?= $form->field($model, 'type')->dropDownList($model->objectTypeList()) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true, 'value' => $city]) ?>
    <?= $form->field($model, 'city_en')->textInput(['maxlength' => true, 'value' => $city_en]) ?>
    <?= $form->field($model, 'city_ky')->textInput(['maxlength' => true, 'value' => $city_ky]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'value' => $address]) ?>
    <?= $form->field($model, 'address_en')->textInput(['maxlength' => true, 'value' => $address_en]) ?>
    <?= $form->field($model, 'address_ky')->textInput(['maxlength' => true, 'value' => $address_ky]) ?>

    <!-- <label class="form__container" id="upload-container">Choose or Drag & Drop Files
        <?php //$form->field($model, 'images[]')->fileInput(['multiple' => true, 'accept' => 'image/*', 'class' => 'form__file']) ?>
    </label>
    <div class="form__files-container" id="files-list-container"></div> -->

    <?php
    $initial_preview = false;
    if ($model->isImageSet()) {
        $initial_preview = $model->imagesPreview();
    }
    $images = $model->getImages(); // Get all images
    
    echo $form->field($model, 'images[]')->widget(
        \kartik\file\FileInput::class,
        [
            'options' => ['accept' => 'image/*', 'multiple' => true, 'id' => 'object-images',],
            'pluginOptions' => [
                'class' => 'form-control object-image-upload',
                'previewFileType' => 'image',
                'initialPreview' => $initial_preview,
                'initialPreviewConfig' => array_map(function ($image) use ($model) {
                    return [
                        'url' => Url::to(['/object/remove-image', 'image_id' => $image->id, 'object_id' => $model->id]),
                        'key' => $image->id,
                        'extra' => [
                            'main' => $image->id,
                        ],
                    ];
                }, $images),
                'overwriteInitial' => false,
                'initialPreviewAsData' => true,
                'initialPreviewFileType' => 'image',
                'initialPreviewShowDelete' => true,
                'showRemove' => false,
                'showUpload' => false,
                'browseOnZoneClick' => true,
                'initialCaption' => '',
                'fileActionSettings' => [
                    'showZoom' => false,
                ],
                'otherActionButtons' => '
                    <button type="button" class="kv-cust-btn img-main btn btn-sm" title="Set as main">
                        <i class="glyphicon glyphicon-ok"></i>
                    </button>
                ',
            ],
        ]
    )->label(Yii::t('app', 'Фотографии'));

    // echo $form->field($model, 'images[]')->widget(FileInput::classname(), [
    //     'options' => ['multiple' => true, 'accept' => 'image/*'],
    //     'pluginOptions' => [
    //         'previewFileType' => 'image',
    //         'showUpload' => false,
    //         'showRemove' => true,
    //         'overwriteInitial' => false,
    //         'initialPreviewAsData' => true,
    //         'dropZoneEnabled' => true,
    //     ],
    // ]);
    
    ?>


    <?php //= $form->field($model, 'features')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'site')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'check_in')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'check_out')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'reception')->checkbox() ?>


    <?php
    // $model->description = $model->description[0];
    // echo $form->field($model, 'description')->widget(
    //     Widget::className(),
    //     [
    //         'settings' => [
    //             'lang' => 'ru',
    //             'minHeight' => 200,
    //             'formatting' => ['p', 'blockquote', 'h2'],
    //             'imageCaption' => true,
    //             'imageUpload' => Url::to(['site/image-upload']),
    //             'fileUpload' => Url::to(['site/file-upload']),
    //             'plugins' => [
    //                 'imagemanager',
    //                 'filemanager',
    //                 'clips',
    //                 'fullscreen',
    //                 'table',
    //                 'fontsize',
    //                 'fontcolor',
    //                 'video',
    //             ],
    //         ],
    //     ]
    // ); ?>

    <?php
    echo $form->field($model, 'description')->textarea(['value' => $description]);
    echo $form->field($model, 'description_en')->textarea(['value' => $description_en]);
    echo $form->field($model, 'description_ky')->textarea(['value' => $description_ky]);

    // $model->description_en = $model->description[1];
    // echo $form->field($model, 'description_en')->widget(
    //     Widget::className(),
    //     [
    //         'settings' => [
    //             'lang' => 'ru',
    //             'minHeight' => 200,
    //             'formatting' => ['p', 'blockquote', 'h2'],
    //             'imageCaption' => true,
    //             'imageUpload' => Url::to(['site/image-upload']),
    //             'fileUpload' => Url::to(['site/file-upload']),
    //             'plugins' => [
    //                 'imagemanager',
    //                 'filemanager',
    //                 'clips',
    //                 'fullscreen',
    //                 'table',
    //                 'fontsize',
    //                 'fontcolor',
    //                 'video',
    //             ]
    //         ],
    //     ]
    // ); ?>

    <?php
    // $model->description_ky = $model->description[2];
    // echo $form->field($model, 'description_ky')->widget(
    //     Widget::className(),
    //     [
    //         'settings' => [
    //             'lang' => 'ru',
    //             'minHeight' => 200,
    //             'formatting' => ['p', 'blockquote', 'h2'],
    //             'imageCaption' => true,
    //             'imageUpload' => Url::to(['site/image-upload']),
    //             'fileUpload' => Url::to(['site/file-upload']),
    //             'plugins' => [
    //                 'imagemanager',
    //                 'filemanager',
    //                 'clips',
    //                 'fullscreen',
    //                 'table',
    //                 'fontsize',
    //                 'fontcolor',
    //                 'video',
    //             ]
    //         ],
    //     ]
    // ); ?>

    <div id="map" style="width: 100%; height: 400px;"></div>
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
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
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
            $('#objects-lat').val(coords[0]);
            $('#objects-lon').val(coords[1]);
        });

        // Update coordinates initially
        $('#objects-lat').val(placemark.geometry.getCoordinates()[0]);
        $('#objects-lon').val(placemark.geometry.getCoordinates()[1]);
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


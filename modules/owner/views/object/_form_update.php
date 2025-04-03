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

<div class="oblast-form">

    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]);
    $name_list = $model->name;
    $city_list = $model->city;
    $address_list = $model->address;
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'value' => $name_list[0]]) ?>
    <?= $form->field($model, 'name_en')->textInput(['maxlength' => true, 'value' => $name_list[1]]) ?>
    <?= $form->field($model, 'name_ky')->textInput(['maxlength' => true, 'value' => $name_list[2]]) ?>
    <?= $form->field($model, 'type')->dropDownList($model->objectTypeList()) ?>
    
    <?= $form->field($model, 'city')->textInput(['maxlength' => true, 'value' => $city_list[0]]) ?>
    <?= $form->field($model, 'city_en')->textInput(['maxlength' => true, 'value' => $city_list[1]]) ?>
    <?= $form->field($model, 'city_ky')->textInput(['maxlength' => true, 'value' => $city_list[2]]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'value' => $address_list[0]]) ?>
    <?= $form->field($model, 'address_en')->textInput(['maxlength' => true, 'value' => $address_list[1]]) ?>
    <?= $form->field($model, 'address_ky')->textInput(['maxlength' => true, 'value' => $address_list[2]]) ?>
    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>


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
    echo $form->field($model, 'description')->textarea(['value'=>$model->description[0]]);
    echo $form->field($model, 'description_en')->textarea(['value'=>$model->description[1]]);
    echo $form->field($model, 'description_ky')->textarea(['value'=>$model->description[2]]);

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
</style>
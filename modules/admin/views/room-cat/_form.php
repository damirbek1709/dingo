<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RoomCat $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="room-cat-form">

    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_en')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_ky')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'guest_amount')->textInput() ?>

    <?= $form->field($model, 'similar_room_amount')->textInput() ?>

    <?= $form->field($model, 'area')->textInput() ?>

    <?= $form->field($model, 'bathroom')->checkbox() ?>

    <?= $form->field($model, 'balcony')->checkbox() ?>

    <?= $form->field($model, 'air_cond')->checkbox() ?>

    <?= $form->field($model, 'kitchen')->checkbox() ?>

    <?= $form->field($model, 'base_price')->textInput() ?>

    <?php echo $form->field($model, 'img')->hiddenInput()->label(false); ?>

    <?php
    $initial_preview = false;
    if (!$model->isNewRecord && $model->isImageSet()) {
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
                        'url' => Url::to(['/room-cat/remove-image', 'image_id' => $image->id, 'product_id' => $model->id]),
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
    ); ?>



    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?= Html::errorSummary($model) ?>

    <?php ActiveForm::end(); ?>

</div>

<script>
    var mainImgIdField = $('#roomcat-img');
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
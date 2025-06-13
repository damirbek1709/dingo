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
    <?php echo $this->render('../top_nav', ['model' => $model, 'object_id' => $object_id]); ?>
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]);
    ?>
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
                    <h1 class="general_title"><?= $title[0] ?></h1>
                    <div class="drop-zone" id="drop-zone">
                        <div class="drop-icon"></div>
                        <div class="drop-text-top">
                            <?php echo Yii::t('app', 'Нажмите или перетащите файл в эту область для загрузки') ?>
                        </div>
                        <div class="drop-text-bottom">
                            <?php echo Yii::t('app', 'Поддержка одиночной или массовой загрузки') ?>
                        </div>
                        <?= $form->field($model, 'images[]', [
                            'template' => '{input}', // Hides label and wrapper
                        ])->fileInput([
                                    'multiple' => true,
                                    'style' => 'display: none;',
                                    'id' => 'file-input'
                                ]) ?>

                    </div>

                    <div class="preview-container" id="preview-container">
                        <?php
                        if ($picture_list): ?>
                            <?php foreach ($picture_list as $index => $image): ?>
                                <?php if ($image): ?>
                                    <div class="preview<?= $index === 0 ? ' main' : '' ?>" id="<?= $image->id; ?>">
                                        <?php if ($index === 0): ?>
                                            <div class="main-label"><?= Yii::t('app', 'Главная') ?></div>
                                        <?php else: ?>
                                            <span class="make-main" type="button"
                                                onclick="makeMain(this.parentElement, <?= $image->id ?>)" id="<?= $image->id ?>">
                                                <?= Yii::t('app', 'Сделать главной') ?>
                                            </span>

                                            <span class="remove_photo" type="button" image_id="<?= $image->id ?>">
                                                <span class="remove_icon"></span>
                                            </span>
                                        <?php endif; ?>

                                        <?= Html::img($image->getUrl('300x200'), ['image_id' => $image->id]) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?= $form->field($model, 'main_img')->hiddenInput(['id' => 'main-img-input'])->label(false) ?>


                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'save-button']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>


        </div>

    </div>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    const mainImgInput = document.getElementById('main-img-input');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    function handleFiles(files) {
        [...files].forEach((file) => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.classList.add('preview');

                const uniqueId = 'new_' + file.name.replace(/\W/g, '_') + '_' + Math.random().toString(36).substr(2, 5);
                div.setAttribute('id', uniqueId);

                const img = document.createElement('img');
                img.src = e.target.result;
                div.appendChild(img);

                if (!document.querySelector('.preview.main')) {
                    div.classList.add('main');
                    div.insertAdjacentHTML('afterbegin', `<div class="main-label">Главная</div>`);
                    mainImgInput.value = uniqueId;
                } else {
                    const button = document.createElement('span');
                    button.className = 'make-main';
                    button.textContent = 'Сделать главной';
                    button.setAttribute('type', 'button');
                    button.onclick = () => makeMain(div, uniqueId);
                    div.appendChild(button);
                }

                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }


    function makeMain(div, id = null) {
        [...previewContainer.children].forEach(child => {
            child.classList.remove('main');
            const label = child.querySelector('.main-label');
            if (label) label.remove();
        });

        div.classList.add('main');
        div.insertAdjacentHTML('afterbegin', `<div class="main-label">Главная</div>`);

        const imageId = id || div.getAttribute('id');
        mainImgInput.value = imageId;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const mainPreview = document.querySelector('.preview.main');
        if (mainPreview) {
            const mainId = mainPreview.getAttribute('id');
            if (mainId && mainImgInput) {
                mainImgInput.value = mainId;
            }
        }
    });

    $('.remove_photo').on('click', function () {
        var image_id = $(this).attr('image_id');
        var model_id = "<?= $model->id ?>";
        var parent = $(this).parent();
        $.ajax({
            method: "GET",
            url: "<?= Yii::$app->urlManager->createUrl('/owner/object/remove-room-image') ?>",
            // beforeSend: function(xhr) {
            //     xhr.setRequestHeader('Authorization', "Bearer " + auth_key);
            // },
            data: {
                image_id: image_id,
                model_id: model_id,
                _csrf: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response == "true") {
                    parent.fadeOut();
                }
                //thisOne.removeClass('post-view-fav');
            }
        });
    });

</script>
<style>
    .main {
        background-color: green;
        color: #fff;
    }
</style>
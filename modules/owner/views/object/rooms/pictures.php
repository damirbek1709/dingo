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
        <div class="back_link"><?= Html::a(Yii::t('app', 'К списку номеров'), ['room-list', 'object_id' => $object_id]) ?>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('room_nav', ['room_id' => $room_id, 'object_id' => $object_id]); ?>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <h1 class="general_title"><?= $title ?></h1>
                    <div class="drop-zone" id="drop-zone">
                        <div class="drop-icon"></div>
                        <div class="drop-text-top">Нажмите или перетащите файл в эту область для загрузки</div>
                        <div class="drop-text-bottom">Поддержка одиночной или массовой загрузки</div>
                        <?= $form->field($model, 'images[]', [
                            'template' => '{input}', // Hides label and wrapper
                        ])->fileInput([
                                    'multiple' => true,
                                    'style' => 'display: none;',
                                    'id' => 'file-input'
                                ]) ?>
                    </div>

                    <div class="preview-container" id="preview-container">
                        <?php if ($model->getImages()): ?>
                            <?php foreach ($model->getImages() as $index => $image): ?>
                                <?php if ($image && method_exists($image, 'getUrl')): ?>
                                    <div class="preview<?= $index === 0 ? ' main' : '' ?>">
                                        <?php if ($index === 0): ?>
                                            <div class="main-label"><?= Yii::t('app', 'Главная') ?></div>
                                        <?php else: ?>
                                            <button class="make-main" type="button" onclick="makeMain(this.parentElement)"
                                                id="<?= $image->id ?>">
                                                <?= Yii::t('app', 'Сделать главной') ?>
                                            </button>
                                        <?php endif; ?>

                                        <?= Html::img($image->getUrl('300x200')) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php

                    // $initial_preview = $model->imagesPreview();
                    // $images = $model->getImages();
                    
                    // echo $form->field($model, 'images[]')->widget(
                    //     \kartik\file\FileInput::class,
                    //     [
                    //         'options' => ['accept' => 'image/*', 'multiple' => 'true'],
                    //         'pluginOptions' => [
                    //             'previewFileType' => 'image',
                    //             'initialPreview' => $initial_preview,
                    //             'initialPreviewConfig' => array_map(function ($image) use ($model) {
                    //                 return [
                    //                     'url' => Url::to(['object/remove-room-image', 'image_id' => $image->id, 'model' => 'RoomCat', 'model_id' => $model->id]),
                    //                     'key' => $image->id,
                    //                     'extra' => [
                    //                         'main' => $image->id,
                    //                     ],
                    //                 ];
                    //             }, $images),
                    //             'overwriteInitial' => false,
                    //             'initialPreviewAsData' => true,
                    //             'initialPreviewFileType' => 'image',
                    //             'initialPreviewShowDelete' => true,
                    //             'showRemove' => false,
                    //             'showUpload' => false,
                    //             'otherActionButtons' => '
                    //             <button type="button" class="kv-cust-btn img-main btn btn-sm" title="Set as main">
                    //                 <i class="glyphicon glyphicon-ok"></i>
                    //             </button>
                    //             ',
                    //             'fileActionSettings' => [
                    //                 'showZoom' => false,
                    //             ],
                    //         ],
                    //     ]
                    // ); ?>

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
    var mainImgIdField = $('#roomcat-img');
    $('body').on('click', '.img-main', function () {
        var imgId = $(this).siblings('.kv-file-remove').attr('data-key');
        mainImgIdField.val(imgId);
        $('.file-preview-thumbnails .file-preview-frame').removeClass('main');
        $('.img-main').removeClass('main');
        $(this).addClass('main');
    });


    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    let mainImageIndex = 0;

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
        [...files].forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.classList.add('preview');

                if (!document.querySelector('.preview.main')) {
                    div.classList.add('main');
                    div.innerHTML += `<div class="main-label">Главная</div>`;
                } else {
                    const button = document.createElement('button');
                    button.className = 'make-main';
                    button.textContent = 'Сделать главной';
                    button.onclick = () => makeMain(div);
                    div.appendChild(button);
                }

                div.innerHTML += `<img src="${e.target.result}" alt="preview">`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }


    function makeMain(div) {
        [...previewContainer.children].forEach(child => {
            child.classList.remove('main');
            const label = child.querySelector('.main-label');
            if (label) label.remove();
        });
        div.classList.add('main');
        div.insertAdjacentHTML('afterbegin', `<div class="main-label">Главная</div>`);
    }

    function save() {
        alert('Файлы сохранены (логика сохранения не реализована)');
    }
</script>
<style>
    .main {
        background-color: green;
        color: #fff;
    }
</style>
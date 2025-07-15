<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\editors\Summernote;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\models\Oblast $model */
/** @var yii\widgets\ActiveForm $form */
//$model->city_id = $model->city ? $model->city[0] : "";
?>


<div class="col-md-6">
    <div class="row">
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


        <?= $form->field($model, 'city_id')->widget(Select2::class, [
            'initValueText' => $initCityText,
            'options' => [
                'placeholder' => 'Введите город или село...',
                'class' => 'form-input'
            ],
            'pluginOptions' => [
                'minimumInputLength' => 2,
                'ajax' => [
                    'url' => Url::to(['/site/search-regions']),
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                    'processResults' => new JsExpression('function (data) {
                return {
                    results: $.map(data.results, function (item) {
                        return {
                            id: item.id,
                            text: item.display
                        };
                    })
                };
            }'),
                ],
            ],
        ]) ?>

        <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес')]) ?>
        <div class="address_hint"><b>Пример:</b>Улица Комсомольская 27</div>

        <?= $form->field($model, 'address_en')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес английском')]) ?>
        <div class="address_hint"><b>Пример:</b> 27 Komsomolskaya street</div>

        <?= $form->field($model, 'address_ky')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Укажите адрес кыргызском')]) ?>
        <div class="address_hint"><b>Пример:</b> Комсомольская 27 кочосу.</div>
    </div>
</div>


<div class="col-md-6">
    <div class="col-md-12">
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
    </div>
</div>

<div class="col-md-12">
    <div class="row">
        <?php
        echo $form->field($model, 'description')->widget(Summernote::class, [
            'useKrajeePresets' => true,
        ]);
        echo $form->field($model, 'description_en')->widget(Summernote::class, [
            'useKrajeePresets' => true,
        ]);
        echo $form->field($model, 'description_ky')->widget(Summernote::class, [
            'useKrajeePresets' => true,
        ]); ?>
    </div>
</div>

<div class="col-md-12">
    <div class="row">
        <?php
        $images = $model->getImages();
        echo $form->field($model, 'img')->hiddenInput(['class' => 'img_for_main'])->label(false);
        ?>
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
            <?php
            if ($images): ?>
                <?php foreach ($images as $index => $image): ?>
                    <?php if ($image && method_exists($image, 'getUrl')): ?>
                        <div class="preview<?= $index === 0 ? ' main' : '' ?>" id="<?= $image->id; ?>">
                            <?php if ($index === 0): ?>
                                <div class="main-label"><?= Yii::t('app', 'Главная') ?></div>
                            <?php else: ?>
                                <span class="make-main" type="button" onclick="makeMain(this.parentElement, <?= $image->id ?>)"
                                    id="<?= $image->id ?>">
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
    </div>
</div>

<!-- <div class="col-md-6">
    
</div> -->

<div class="col-md-6">
    <div class="row">
        <?php //= $form->field($model, 'features')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'phone', [
            'template' => '
        <label class="control-label" for="phone">{label}</label>
        <div class="input-group">
            <span class="input-group-text">+996</span>
            {input}
        </div>
        {error}
    '
        ])->widget(MaskedInput::class, [
                    'mask' => '999 99 99 99',
                    'options' => [
                        'class' => 'form-input',
                        'placeholder' => '___ __ __ __',
                    ],
                ]) ?>
        <?= $form->field($model, 'site')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Сайт')]) ?>
        <?= $form->field($model, 'check_in')->input('time', ['placeholder' => Yii::t('app', 'Заезд'), 'style' => 'width:150px']) ?>
        <?= $form->field($model, 'check_out')->input('time', ['placeholder' => Yii::t('app', 'Выезд'), 'style' => 'width:150px']) ?>

        <?= $form->field($model, 'general_room_count')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Общее количество комнат')]) ?>
        <?= $form->field($model, 'reception')->checkbox() ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'E-mail')]) ?>
        <?php echo $form->field($model, 'img')->hiddenInput()->label(false); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>




<script>
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
        const previewContainer = document.getElementById('preview-container');

        [...files].forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.classList.add('preview');

                if (!document.querySelector('.preview.main')) {
                    div.classList.add('main');
                    div.innerHTML += `<div class="main-label">Главная</div>`;
                } else {
                    const button = document.createElement('span');
                    button.className = 'make-main';
                    button.textContent = 'Сделать главной';
                    button.onclick = () => makeMain(div, file.name);  // You can replace 'file.name' with the actual image ID
                    div.appendChild(button);
                }

                div.innerHTML += `<img src="${e.target.result}" alt="preview">`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    $('.remove_photo').on('click', function () {
        var image_id = $(this).attr('image_id');
        var object_id = "<?= $model->id ?>";
        var parent = $(this).parent();
        $.ajax({
            method: "POST",
            url: "<?= Yii::$app->urlManager->createUrl('/owner/object/remove-object-image') ?>",
            // beforeSend: function(xhr) {
            //     xhr.setRequestHeader('Authorization', "Bearer " + auth_key);
            // },
            data: {
                image_id: image_id,
                object_id: object_id,
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


    function makeMain(div, image_id) {
        const previewContainer = document.getElementById('preview-container');

        // Remove 'main' class from all previews and remove 'main-label'
        [...previewContainer.children].forEach(child => {
            child.classList.remove('main');
            const label = child.querySelector('.main-label');
            if (label) label.remove();
        });

        // Add 'main' class to the selected preview and insert the 'Главная' label
        div.classList.add('main');
        div.insertAdjacentHTML('afterbegin', `<div class="main-label">Главная</div>`);

        // Set the image ID to the hidden input field with id 'objects-img'
        document.getElementById('objects-img').value = image_id; // This will set the value of the hidden input
    }

    function save() {
        alert('Файлы сохранены (логика сохранения не реализована)');
    }

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
        margin: 5px 0 25px;
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

    .select2-selection {
        border-radius: 20px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
    }

    .address_hint {
        margin-bottom: 30px;
        margin-top: -20px;
    }

    .time-input-container {
        max-width: 300px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .time-input-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
    }

    .time-scroll-container {
        position: relative;
        width: 60px;
        height: 120px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        transition: border-color 0.3s ease;
    }

    .time-scroll-container:hover {
        border-color: #007acc;
    }

    .time-scroll-container:focus-within {
        border-color: #007acc;
        box-shadow: 0 0 0 3px rgba(0, 122, 204, 0.1);
    }

    .time-scroll {
        height: 100%;
        overflow-y: scroll;
        scroll-behavior: smooth;
        scroll-snap-type: y mandatory;
        padding: 40px 0;
        -webkit-overflow-scrolling: touch;
    }

    .time-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .time-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .time-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }

    .time-scroll::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    .time-option {
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        scroll-snap-align: center;
        transition: all 0.2s ease;
        user-select: none;
    }

    .time-option:hover {
        background: #f0f8ff;
        color: #007acc;
    }

    .time-option.selected {
        color: #007acc;
        font-weight: 600;
        background: #f0f8ff;
        transform: scale(1.05);
    }

    .time-separator {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin: 0 5px;
    }

    .time-label {
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .selected-time {
        text-align: center;
        margin-top: 20px;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .time-scroll-container::before,
    .time-scroll-container::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 40px;
        pointer-events: none;
        z-index: 1;
    }

    .time-scroll-container::before {
        top: 0;
        background: linear-gradient(to bottom, rgba(255, 255, 255, 0.9), transparent);
    }

    .time-scroll-container::after {
        bottom: 0;
        background: linear-gradient(to top, rgba(255, 255, 255, 0.9), transparent);
    }

    @media (max-width: 768px) {
        .form-input {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .time-input-container {
            margin: 0 10px;
            padding: 15px;
        }

        .time-scroll-container {
            width: 50px;
            height: 100px;
        }

        .time-option {
            height: 35px;
            font-size: 14px;
        }
    }
</style>
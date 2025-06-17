<?php

use yii\widgets\DetailView;
use yii\helpers\Html;
use app\models\Objects;
use yii\web\NotFoundHttpException;
/* @var $this yii\web\View */
/* @var $model app\models\MeilisearchModel */


$title = $model->name;

$this->title = $title[0];
// $this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $title;
?>

<div class="oblast-update">

    <?php echo $this->render('top_nav', ['model' => $model,'object_id'=>$object_id]); ?>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>
            <div class="col-md-9 mob_card_cover">
                <div class="card">
                    <div class="header">
                        <div>
                            <div class="section-label">Название</div>
                            <h2 class="section-value"><?= $title[0]; ?></h2>
                        </div>

                        <button class="edit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <?= Html::a('Редактировать', ['update', 'object_id' => $model->id], ['class' => '']) ?>

                        </button>
                    </div>


                    <div class="col-md-6">
                        <div class="row">
                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Адрес') ?></div>
                                <div class="section-value"><?= $model->address[0]; ?></div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Тип объекта') ?></div>
                                <div class="section-value"><?= $model->objectTypeString(); ?></div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Учредительные документы компании') ?>
                                    <div class="tooltip-container">
                                        <span class="info-icon"></span>
                                        <div class="tooltip">
                                            <?= Yii::t('app', 'Загрузите все документы подтверждающие статус юридического лица (патент, свидетельство, паспорт и тд)'); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php $ceo_docs = $model->getCeoDocs();
                                if ($ceo_docs) {
                                    foreach ($ceo_docs as $doc) {
                                        echo Html::beginTag('div', ['class' => 'ceo_doc_cover']);
                                        echo Html::a($doc['name'], $doc['link'], ['class' => 'ceo_doc']);
                                        echo Html::tag('span', '', ['class' => 'doc_delete_icon', 'name' => $doc['name'], 'folder' => 'ceo']);
                                        echo Html::endTag('div');
                                    }
                                }
                                ?>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Банковские Реквизиты') ?>
                                    <div class="tooltip-container">
                                        <span class="info-icon"></span>
                                        <div class="tooltip">
                                            <?= Yii::t('app', 'Загрузите ваши банковские данные для осуществления выплат на ваш счет'); ?>
                                        </div>
                                    </div>
                                </div>

                                <?php $financial_docs = $model->getFinancialDocs();
                                if ($financial_docs) {
                                    foreach ($financial_docs as $doc) {
                                        echo Html::beginTag('div', ['class' => 'ceo_doc_cover']);
                                        echo Html::a($doc['name'], $doc['link'], ['class' => 'ceo_doc']);
                                        echo Html::tag('span', '', ['class' => 'doc_delete_icon', 'name' => $doc['name'], 'folder' => 'financial']);
                                        echo Html::endTag('div');
                                    }
                                }
                                ?>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Количество номеров') ?></div>
                                <div class="section-value"><?= $model->general_room_count; ?></div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Валюта') ?></div>
                                <div class="section-value"><?= $model->currency ? $model->currency : 'KGS'; ?></div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Описание') ?></div>
                                <div class="section-value"><?= Objects::attributeIndexed($model->description); ?></div>
                            </div>

                            <div class="section">
                                <div class="check-in-out">
                                    <div class="time-section">
                                        <div class="section-label"><?= Yii::t('app', 'Заезд') ?></div>
                                        <div class="section-value"><?= $model->check_in; ?></div>
                                    </div>
                                    <div class="time-section">
                                        <div class="section-label"><?= Yii::t('app', 'Выезд') ?></div>
                                        <div class="section-value"><?= $model->check_out ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="section">
                                <legend><?=Yii::t('app', 'Контактная информация')?></legend>
                                <div class="check-in-out">
                                    <div class="time-section">
                                        <div class="section-label"><?= Yii::t('app', 'Контактный телефон') ?></div>
                                        <div class="section-value">+996 <?= $model->phone; ?></div>
                                    </div>
                                    <div class="time-section">
                                        <div class="section-label"><?= Yii::t('app', 'E-mail') ?></div>
                                        <div class="section-value"><?= $model->email ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Сайт объекта') ?></div>
                                <div class="section-value"><?=$model->site; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row" style="padding:0 0 0 15px">
                            <div class="photo-grid">
                                <?php
                                $counter = 0;
                                foreach ($model->getImages() as $image): ?>
                                    <div class="photo-item">
                                        <?php echo Html::img($image->getUrl('260x180'), ['class' => 'view-thumbnail-img']); ?>
                                        <!-- <div class="main-photo-badge">Главная</div> -->
                                    </div>
                                    <?php
                                    $counter++;
                                    if ($counter >= 4) {
                                        break;
                                    }
                                endforeach;
                                ?>
                            </div>
                            <div class="photo-actions">
                                <button class="photo-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>

                                    <div class="photo-gallery-button">
                                        <?= Html::tag('span', Yii::t('app', 'Все фото'), [
                                            'class' => 'show-all-photos',
                                            'id' => 'showAllPhotosBtn'
                                        ]) ?>
                                    </div>
                                </button>
                                <!-- <button class="add-btn">+</button> -->
                                <?= $this->render('gallery', ['model' => $model]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.doc_delete_icon').on('click', function () {
        var name = $(this).attr('name');
        var folder = $(this).attr('folder');
        var object_id = "<?= $model->id ?>";
        var parent = $(this).parent();

        $.ajax({
            url: "<?= Yii::$app->urlManager->createUrl('/owner/object/remove-file') ?>", // Your action URL
            type: 'POST',
            data: {
                name: name,  // Send the tariff ID
                folder: folder,
                object_id: object_id,   // Send the checked state
                _csrf: $('meta[name="csrf-token"]').attr('content')  // CSRF token for security (if needed)
            },
            success: function (response) {
                if (response == 'true') {
                    console.log('File was removed');
                    parent.fadeOut();
                } else {
                    console.log('File was not removed');
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX error: ' + status + ' ' + error);
            }
        });
    });
</script>
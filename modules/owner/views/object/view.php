<?php

use yii\widgets\DetailView;
use yii\helpers\Html;
use app\models\Objects;
/* @var $this yii\web\View */
/* @var $model app\models\MeilisearchModel */

$name_list = $model->name;
$title = $name_list[0];
$this->title = $title;
// $this->params['breadcrumbs'][] = ['label' => 'Объекты', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $title;
?>

<div class="oblast-update">

    <?php echo $this->render('top_nav', ['model' => $model]); ?>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>
            <div class="col-md-9">

                <div class="card">

                    <div class="header">
                        <div>
                            <div class="section-label">Название</div>
                            <h2 class="section-value"><?= $title; ?></h2>
                        </div>

                        <button class="edit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => '']) ?>

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
                                    <span class="info-icon">i</span>
                                </div>
                                <button class="upload-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    Загрузка
                                </button>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Банковские Реквизиты') ?> <span
                                        class="info-icon">i</span></div>
                                <button class="upload-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    Загрузка
                                </button>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Количество номеров') ?></div>
                                <div class="section-value"><?= $model->general_room_count; ?></div>
                            </div>

                            <div class="section">
                                <div class="section-label"><?= Yii::t('app', 'Валюта') ?></div>
                                <div class="section-value"><?= $model->currency; ?></div>
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
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row" style="padding:0 0 0 15px">
                            <div class="photo-grid">
                                <?php
                                if (count($model->getImages()) > 1):
                                    foreach ($model->getImages() as $image): ?>
                                        <div class="photo-item">
                                            <?php echo Html::img($image->getUrl('260x180'), ['class' => 'view-thumbnail-img']); ?>
                                            <!-- <div class="main-photo-badge">Главная</div> -->
                                        </div>
                                        <?php
                                    endforeach;
                                endif;
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
                                    Все фото
                                </button>
                                <button class="add-btn">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use app\models\Category;
use app\models\CategoryAttribute;
use himiklab\sortablegrid\SortableGridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $categoryAttributeSearchModel app\models\CategoryAttributeSearch */
/* @var $categoryAttributeDataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['minimized']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить категорию?',
                'method' => 'post',
            ],
        ]) ?>
        <?= $model->is_alternative === Category::IS_NOT_ALTERNATIVE ? Html::a('Добавить атрибут', ['category-attribute/create', 'category_id' => $model->id], ['class' => 'btn btn-success']) : '' ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'parent',
                'value' => $model->parentLink,
                'format' => 'raw',
            ],
            'title',
            'title_ky',
            'typeString',
            'isAlternativeString',
            'isModerationNeededString',
            [
                'attribute' => 'alternativeCategories',
                'value' => $model->alternativeCategoriesLinks,
                'format' => 'raw',
            ],
            [
                'attribute' => 'alternativeCategoriesMain',
                'value' => $model->alternativeCategoriesMainLinks,
                'format' => 'raw',
            ],
            'post_limit',
            'post_count',
            'isPostCountVisibleString',
            [
                'attribute' => 'picture',
                'value' => Html::img($model->thumbnailPicture, [
                    'class' => 'img-thumbnail img-rounded',
                ]),
                'format' => 'raw',
            ],
            'description:ntext',
            [
                'attribute' => 'subcategories',
                'value' => $model->subcategoriesLinks,
                'format' => 'raw',
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?php if ($model->is_alternative === Category::IS_NOT_ALTERNATIVE): ?>

        <h2>Атрибуты</h2>

        <?php Pjax::begin(); ?>

        <?php
            $data = [
                'dataProvider' => $categoryAttributeDataProvider,
                'columns' => [
                    [
                        'attribute' => 'title',
                        'enableSorting' => false,
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function (CategoryAttribute $data) {
                            return $data->typeTitle;
                        },
                        'filter' => CategoryAttribute::getTypeList(),
                        'enableSorting' => false,
                    ],
                    [
                        'attribute' => 'is_required',
                        'format' => 'boolean',
                        'filter' => false,
                        'enableSorting' => false,
                    ],
                    [
                        'attribute' => 'is_searchable',
                        'format' => 'boolean',
                        'filter' => false,
                        'enableSorting' => false,
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['category-attribute/view', 'id' => $model->id], [
                                    'title' => 'Просмотр',
                                    'data-pjax' => 0,
                                ]);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['category-attribute/update', 'id' => $model->id], [
                                    'title' => 'Редактировать',
                                    'data-pjax' => 0,
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['category-attribute/delete', 'id' => $model->id], [
                                    'title' => 'Удалить',
                                    'data-pjax' => 0,
                                    'data' => [
                                        'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                                        'method' => 'post',
                                    ],
                                ]);
                            }
                        ],
                    ],
                ],
            ];

            echo SortableGridView::widget($data);
        ?>

        <?php Pjax::end(); ?>

    <?php endif; ?>
</div>

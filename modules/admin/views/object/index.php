<?php

use app\models\Objects;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BusinessAccountBridgeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = Yii::t('app', 'Список объектов');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">

    <?php Pjax::begin(['id' => 'event_post']); ?>
    <div class="object-admin-grid">
        <div class="col-md-12">
            <?php echo $this->render('nav-left'); ?>
        </div>

        <div class="col-md-12">
            <div class="card">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'summary' => false,
                    'columns' => [
                        //'id',
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                if (is_array($model['name'])) {
                                                    //return implode(', ', $model['name']);
                                                    return $model['name'][0];
                                                }
                                                return $model['name'][0];
                                            },
                            'label' => Yii::t('app', 'Название'),
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function ($model) {
                                                if (array_key_exists('type', $model)) {
                                                    return Objects::typeString(1);
                                                } else {
                                                    return "Не задано";
                                                }
                                            },
                            'label' => Yii::t('app', 'Тип объекта'),
                        ],
                        [
                            'attribute' => 'address',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                if (array_key_exists('address', $model)) {
                                                    return $model['address'][0];
                                                } else {
                                                    return "Не задано";
                                                }
                                            },
                            'label' => Yii::t('app', 'Адрес'),
                        ],
                        'email',
                        [
                            'attribute' => 'phone',
                            'value' => function ($model) {
                                                if (array_key_exists('address', $model)) {
                                                    return $model['phone'];
                                                } else {
                                                    return "Не задано";
                                                }
                                            },
                            'label' => Yii::t('app', 'Контакты'),
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($model) {
                                                if (array_key_exists('status', $model)) {
                                                    $status_arr = Objects::statusData($model['status']);
                                                } else {
                                                    $status_arr = Objects::statusData(Objects::STATUS_NOT_PUBLISHED);
                                                }
                                                $color = $status_arr['color'];
                                                return "<span style='border: 1px solid;padding: 2px 6px;border-radius: 4px;background-color: #00000003;color:$color;'>" . $status_arr['label'] . "</span>";
                                            },
                            'label' => Yii::t('app', 'Статус'),
                            'format' => 'raw',
                            'filter' => Html::activeDropDownList(
                                new \yii\base\DynamicModel(['status' => Yii::$app->request->get('status')]),
                                'status',
                                Objects::statusList(),
                                ['class' => 'form-control', 'prompt' => 'Все']
                            ),
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'object_id' => $model['id']], [
                                                        'title' => Yii::t('app', 'View'),
                                                    ]);
                                                },
                                // 'update' => function ($url, $model) {
                                //                 return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $model['id']], [
                                //                     'title' => Yii::t('app', 'Update'),
                                //                 ]);
                                //             },
                                // 'delete' => function ($url, $model) {
                                //                 return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model['id']], [
                                //                     'title' => Yii::t('app', 'Delete'),
                                //                     'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                //                     'data-method' => 'post',
                                //                 ]);
                                //             },
                            ]
                        ],

                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $('.approve').on('click', function () {
        var id = $(this).attr('data-id');
        var auth_key = $('.top-profile-link').attr('data-user-id');
        $.ajax({
            method: "POST",
            url: "<?= Yii::$app->urlManager->createUrl('/admin/business-account-bridge/approve') ?>",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', "Bearer " + auth_key);
            },
            data: {
                id: id,
            },
            success: function (response) {
                if (response == "true") {
                    alert("Одобрено");
                    $.pjax.reload({
                        container: "#event_post"
                    });
                }
                //thisOne.removeClass('post-view-fav');
            }
        });
    });
</script>
<style>
    .approve::before {
        content: '\2714';
        width: 30px;
        height: 20px;
        color: green;
        cursor: pointer;
        font-weight: bold;
    }

    .deny::before {
        content: '\2716';
        width: 30px;
        height: 20px;
        color: red;
        cursor: pointer;
        font-weight: bold;
    }
</style>
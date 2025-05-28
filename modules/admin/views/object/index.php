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
                                                return Objects::typeString($model['type']);
                                            },
                            'label' => Yii::t('app', 'Тип объекта'),
                        ],
                        [
                            'attribute' => 'address',
                            'format' => 'raw',
                            'value' => function ($model) {
                                                if (is_array($model['address'])) {
                                                    return $model['address'][0];
                                                }
                                                return $model['address'];
                                            },
                            'label' => Yii::t('app', 'Адрес'),
                        ],

                        [
                            'label' => 'Имя хоста',
                            'attribute' => 'host_name',
                            'value' => function ($model) {
                                                return Objects::hostName($model['user_id']);
                                            },
                        ],
                        'email',
                        [
                            'attribute' => 'phone',
                            'value' => function ($model) {
                                                return $model['phone'];
                                            },
                            'label' => Yii::t('app', 'Контакты'),
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($model) {
                                                $status_arr = Objects::statusData($model['status']);
                                                $color = $status_arr['color'];
                                                return "<span style='border: 1px solid;padding: 2px 6px;border-radius: 4px;background-color: #00000003;color:$color;'>" . $status_arr['label'] . "</span>";
                                            },
                            'label' => Yii::t('app', 'Статус'),
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'min-width: 190px;'],
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
                            'header' => 'Действие',
                            'buttons' => [
                                'view' => function ($url, $model) {
                                                    $string = "Просмотр";
                                                    if ($model['status'] == Objects::STATUS_ON_MODERATION) {
                                                        $string = "Модерация";
                                                    }
                                                    return Html::a($string, ['/admin/object/view', 'object_id' => $model['id']], [
                                                        'class' => 'table_action_button',
                                                        'title' => Yii::t('app', $string),
                                                    ]);
                                                },
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

    .table_action_button {
        border: 1px solid #333;
        color: #333;
        border-radius: 4px;
        padding: 1px 6px;
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
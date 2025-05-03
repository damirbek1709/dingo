<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BusinessAccountBridgeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Список объектов');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-account-bridge-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <?php Pjax::begin(['id' => 'event_post']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model) {
                    if (is_array($model['name'])) {
                        return implode(', ', $model['name']);
                    }
                    return $model['name'];
                },
            ],
            'email',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $model['id']], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['update', 'id' => $model['id']], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model['id']], [
                            'title' => Yii::t('app', 'Delete'),
                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                            'data-method' => 'post',
                        ]);
                    },
                ]
            ],
            'status',
        ],
    ]); ?>



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
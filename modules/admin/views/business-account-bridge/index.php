<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BusinessAccountBridgeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Заявки на пробный бизнес аккаунт');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-account-bridge-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <?php Pjax::begin(['id' => 'event_post']); ?>
    <?= GridView::widget([
        'summary' => false,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'user_id',
                'value' => function ($model) {
                    return Html::a($model->user->username, ["/user/{$model->user_id}"]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'active_until',
                'value' => function ($model) {
                    return date('d.m.Y', strtotime($model->active_until));
                }
            ],
            [
                'attribute' => 'business_account_id',
                'value' => function ($model) {
                    return $model->businessAccount->name;
                }
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->statusString;
                }
            ],

            [
                'template' => '{approve}{delete}',
                'class' => 'yii\grid\ActionColumn',
                'urlCreator' => function ($action, $model, $key, $index, $column) {
                    return Url::toRoute(["/post/{$action}", 'id' => $model->id]);
                },
                'buttons' => [
                    'approve' => function ($url, $model, $key) {     // render your custom button
                        return Html::tag('span', '', ['class' => 'approve', 'title' => 'Одобрить', 'data-id' => $model->id]);
                    },
                ]
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>


</div>

<script>
    $('.approve').on('click', function() {
        var id = $(this).attr('data-id');
        var auth_key = $('.top-profile-link').attr('data-user-id');
        $.ajax({
            method: "POST",
            url: "<?= Yii::$app->urlManager->createUrl('/admin/business-account-bridge/approve') ?>",
            beforeSend: function(xhr) {
                xhr.setRequestHeader('Authorization', "Bearer " + auth_key);
            },
            data: {
                id: id,
            },
            success: function(response) {
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
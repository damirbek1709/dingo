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
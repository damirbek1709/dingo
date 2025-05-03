<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\models\Objects;
use yii\widgets\Pjax;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item owner-nav-item-info">
        <?= Html::a(Yii::t('app', 'Информация'), ['view', 'object_id' => $model->id]); ?>
    </div>
    <div class="owner-nav-item owner-nav-item-comfort">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['comfort', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-payment">
        <?= Html::a(Yii::t('app', 'Оплата'), ['payment', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-terms">
        <?= Html::a(Yii::t('app', 'Условия'), ['terms', 'object_id' => $model->id]); ?>
    </div>

    <!-- <div class="owner-nav-item owner-nav-item-crew">
        <?php //echo Html::a(Yii::t('app', 'Сотрудники'), ['crew', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-feedback">
        <?php //echo  Html::a(Yii::t('app', 'Отзывы'), ['feedback', 'object_id' => $model->id]); ?>
    </div> -->
</div>

<?php Pjax::begin(['id' => 'moderation-status-block']); ?>
<?php $status_arr = Objects::statusData($model->status); ?>
<div class="owner-nav-cover row margin_25">
    <div class="status-block" id="<?= $model->id ?>">
        <div class="status-header">
            <div class="status-info" style="color:<?= $status_arr['color'] ?>">
                <div class="status-circle" style="background-color:<?= $status_arr['color'] ?>"></div>
                <div class="status-text"><?= $status_arr['label'] ?></div>
            </div>
            <div class="status-arrow"></div>
        </div>
        <div class="status-description">
            <?= $status_arr['description'] ?>
        </div>
        <div class="save-button" style="width:100%"><?= Yii::t('app', 'Модерировать') ?></div>

    </div>
</div>
<?php Pjax::end(); ?>

<?php
Modal::begin([
    'id' => 'statusModal',
    'size' => 'modal-md',
    'header' => "<h1 class='dialog-title'>" . $status_arr['label'] . "</h1>",
    'options' => ['class' => 'status-gallery-modal'],
]); ?>

<div class="dialog-content">
    <div class="dialog-message">
        Вы проверили все необходимые данные? Опубликуйте объект и он станет доступен для поиска и бронирования. Либо
        отправьте объект на доработку.
    </div>
    <div class="row">
        <div class="col-md-6">
            <button data-send="<?= Objects::STATUS_DENIED; ?>" data-status="<?= $model->status; ?>" style="width:100%;font-size:14px"
                class="save-button moderate-button moderate-button-white">Отправить на доработку</button>
        </div>

        <div class="col-md-6">
            <button style="width:100%;font-size:14px" data-send="<?= Objects::STATUS_PUBLISHED; ?>"
                class="save-button moderate-button">Одобрить</button>
        </div>
    </div>
</div>

<?php Modal::end();
?>

<script>
    // Use event delegation for elements that might be reloaded with Pjax
    $(document).on('click', '.save-button', function () {
        $('#statusModal').modal('show');
    });

    var object_id = "<?= $model->id ?>";

    $(document).on('click', '.moderate-button', function () {
        if (!$(this).hasClass('dismiss')) {
            var data_status = parseInt($(this).attr('data-send'));
            $.ajax({
                method: "POST",
                url: "<?= Yii::$app->urlManager->createUrl('/admin/object/send-to-moderation') ?>",
                data: {
                    object_id: object_id,
                    status: data_status
                    //_csrf: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response) {
                        console.log(response);
                        $('.dialog-title').text(response.title);
                        $('.dialog-message').text(response.description);
                        $('.save-button').attr('data-dismiss', "modal").addClass('dismiss');
                    }
                }
            });
        }
        else {
            $.pjax.reload({ container: '#moderation-status-block' });
        }
    });

    // Re-initialize event handlers after pjax content is loaded
    $(document).on('pjax:complete', function () {
        // Any additional initialization code can go here if needed
        console.log('Pjax reload completed');
    });
</script>

<style>
    .dialog-title {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }

    .info-text {
        font-size: 16px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    ul {
        padding-left: 20px;
        margin-bottom: 20px;
    }

    li {
        margin-bottom: 8px;
        font-size: 16px;
    }

    .modal-header {
        padding: 25px 45px 0;
        border-bottom: 0;
    }
</style>
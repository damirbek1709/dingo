<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\models\Objects;
use yii\widgets\Pjax;
?>
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
    <div class="dialog-description"><?= $status_arr['html'] ?></div>
    <?php if ($model->deny_reason) {
        echo Html::tag('div', "<strong>" . Yii::t('app', 'Причина отклонения: ') . "</strong>" . $model->deny_reason, ['class' => 'deny_reason']);
    } ?>
    <?php if ($model->status != Objects::STATUS_ON_MODERATION): ?>
        <div class="dialog-button-cover">
            <button style="width:100%" data-status="<?= $model->status; ?>" class="save-button moderate-button">
                <?= $status_arr['button_text'] ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<?php Modal::end();
?>

<script>
    // Use event delegation for elements that might be reloaded with Pjax
    $(document).on('click', '.status-block', function () {
        $('#statusModal').modal('show');
    });

    var object_id = "<?= $model->id ?>";
    var html = $('.dialog-content').html();

    $(document).on('click', '.moderate-button', function () {
        var data_status = parseInt($(this).attr('data-status'));
        var switcher = false;
        var status_not_published = "<?= Objects::STATUS_NOT_PUBLISHED ?>";
        var action = "<?= Yii::$app->urlManager->createUrl('/owner/object/change-status') ?>";
        if (data_status == "<?= Objects::STATUS_NOT_PUBLISHED ?>") {
            var docs = "<?= Objects::statusCondition($model->id, $model->status)['docs'] ?>";
            var room = "<?= Objects::statusCondition($model->id, $model->status)['room'] ?>";
            var tariff = "<?= Objects::statusCondition($model->id, $model->status)['tariff'] ?>";

            if (docs == 0) {
                window.location.href = '<?= Yii::$app->urlManager->createUrl("/owner/object/update?object_id=$model->id") ?>';
            }
            if (room == 0) {
                window.location.href = '<?= Yii::$app->urlManager->createUrl("owner/object/room-list?object_id=$model->id") ?>';
            }
            if (tariff == 0) {
                window.location.href = '<?= Yii::$app->urlManager->createUrl("owner/object/tariff-list?object_id=$model->id") ?>';
            }
        }
        else if (data_status == "<?= Objects::STATUS_READY_FOR_PUBLISH ?>") {
            $.ajax({
                method: "POST",
                url: "<?= Yii::$app->urlManager->createUrl('/owner/object/send-to-moderation') ?>",
                data: {
                    object_id: object_id,
                    _csrf: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response == "true") {
                        // Update modal content
                        $('.dialog-title').text("<?= Objects::statusData(Objects::STATUS_ON_MODERATION)['label'] ?>");
                        $('.dialog-description').text("<?= Objects::statusData(Objects::STATUS_ON_MODERATION)['description'] ?>");
                        $('.save-button').text("<?= Objects::statusData(Objects::STATUS_ON_MODERATION)['button_text'] ?>").addClass('cancel-button');;
                    }
                }
            });
        }

        else if (data_status == "<?= Objects::STATUS_PUBLISHED; ?>") {
            $('.dialog-title').text("<?= Yii::t('app', 'Снятие с публикации') ?>");
            $('.dialog-description').text("<?= Yii::t('app', 'Ваше объявление снимится с публикации и не будет отображаться на сайте Dingo до момента, когда решите возобновить прием гостей.') ?>");
            $('.dialog-button-cover').html('<div class="row"><div class="col-md-6"><button style="width:100%" data-status="<?= Objects::STATUS_PUBLISHED; ?>" class="save-button moderate-button-white cancel-button">Отмена</button></div><div class="col-md-6"><button style="width:100%" data-status="<?= Objects::STATUS_PUBLISHED; ?>" class="save-button unpublish-button">Снять с публикации</button></div></div>');
            switcher = true;
        }
    });

    $(document).on('click', '.unpublish-button', function () {
        $.ajax({
            method: "POST",
            url: "<?php echo Yii::$app->urlManager->createUrl('/owner/object/unpublish') ?>",
            data: {
                object_id: object_id,
                _csrf: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response == "true") {
                    // Update modal content
                    $('.dialog-title').text("<?php echo Objects::statusData(Objects::STATUS_NOT_PUBLISHED)['label'] ?>");
                    $('.dialog-description').text("<?php echo Objects::statusData(Objects::STATUS_NOT_PUBLISHED)['description'] ?>");
                    $.pjax.reload({ container: '#moderation-status-block' });
                    $('#statusModal').modal('hide');
                }
            }
        });
    });



    $(document).on('click', '.cancel-button', function () {
        $('#statusModal').modal('hide');
    });

    $('#statusModal').on('hide.bs.modal', function (e) {
        $('.dialog-content').html(html);
        $.pjax.reload({ container: '#moderation-status-block' });
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
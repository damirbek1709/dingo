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

        <div class="save-button moderate-button" style="width:100%"><?= Yii::t('app', 'Модерировать') ?></div>

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
    <div class="row action-buttons">



        <?php if ($model->status != Objects::STATUS_PUBLISHED): ?>
            <div class="col-md-6">
                <button data-send="<?= Objects::STATUS_DENIED; ?>" data-status="<?= $model->status; ?>"
                    style="width:100%;font-size:13px"
                    class="save-button moderate-call-button moderate-button-white">Отправить на
                    доработку</button>
            </div>
            <div class="col-md-6">
                <button style="width:100%;font-size:14px" data-send="<?= Objects::STATUS_PUBLISHED; ?>"
                    class="save-button moderate-call-button"><?= Yii::t('app', 'Одобрить') ?></button>
            </div>
        <?php elseif ($model->status == Objects::STATUS_PUBLISHED): ?>
            <div class="col-md-6">
                <button data-send="<?= Objects::STATUS_NOT_PUBLISHED; ?>" data-status="<?= $model->status; ?>"
                    style="width:100%;font-size:13px"
                    class="save-button moderate-call-button moderate-button-white"><?= Yii::t('app', 'Снять с публикации') ?></button>
            </div>
            <div class="col-md-6">
                <button style="width:100%;font-size:14px"
                    class="save-button cancel-button"><?= Yii::t('app', 'Отмена') ?></button>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php Modal::end();
?>

<script>
    // Use event delegation for elements that might be reloaded with Pjax
    $(document).on('click', '.moderate-button', function () {
        $('#statusModal').modal('show');
    });

    var object_id = "<?= $model->id ?>";
    var html = $('.dialog-content').html();

    $(document).on('click', '.moderate-call-button', function () {
        if (!$(this).hasClass('dismiss')) {
            var html = $('.dialog-content').html();
            var data_status = parseInt($(this).attr('data-send'));
            if (data_status == "<?php echo Objects::STATUS_DENIED ?>") {
                $('.dialog-content').html('<div class="dialog-message"><?= Yii::t('app', 'Напишите причину отклонения модерации администратору объекта. Укажите какой информации не хватает для одобрения.') ?>' +
                    '<h3 class="minor_title"><?= Yii::t('app', 'Сообщение хосту (администратору объекта)') ?></h3>' +
                    '<textarea type="text" class="deny_reason form-control" rows="6" id="moderation-reason" placeholder="Введите ваше сообщение"></textarea>' +
                    '</div><div class= "row"><div class="col-md-6">' +
                    '<button style="width:100%;font-size:14px" class="save-button cancel-button moderate-button-white" data-dismiss="modal"><?= Yii::t('app', 'Отменить') ?></button></div>' +
                    '<div class="col-md-6"><button style="width:100%;font-size:13px" data-send="3" class="save-button send-for-revision"><?= Yii::t('app', 'Отправить на доработку') ?></button></div></div>');

                var reason = $('.deny_reason').text();

                $(document).on('click', '.send-for-revision', function () {
                    const message = $('#moderation-reason').val().trim();

                    if (!message) {
                        alert('Пожалуйста, введите сообщение для администратора.');
                        return;
                    }

                    $.ajax({
                        url: "<?= Yii::$app->urlManager->createUrl('/admin/object/send-to-moderation') ?>",  // Replace with your actual URL
                        type: 'POST',
                        data: {
                            object_id: <?= $model->id ?>,       // Assuming you're passing model ID
                            message: message,
                            status: data_status,
                            _csrf: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            $('.dialog-title').text('Вы отклонили запрос на публикацию объекта');
                            $('.dialog-content').html('<div class="dialog-message"><?= Yii::t('app', 'Напишите причину отклонения модерации администратору объекта. Укажите какой информации не хватает для одобрения.') ?>' +
                                '<h3 class="minor_title"><?= Yii::t('app', 'Сообщение хосту (администратору объекта)') ?></h3>' +
                                '<div class="">' + message + '</div>' +
                                '<div class= "row"><div class="col-md-6">' +
                                '<button style="width:100%;font-size:14px" class="save-button cancel-button moderate-button-white" data-dismiss="modal"><?= Yii::t('app', 'Закрыть') ?></button></div></div>');


                        },
                        error: function () {
                            alert('Произошла ошибка при отправке. Попробуйте еще раз.');
                        }
                    });
                });

            }
            else {
                $.ajax({
                    method: "POST",
                    url: "<?= Yii::$app->urlManager->createUrl('/admin/object/send-to-moderation') ?>",
                    data: {
                        object_id: object_id,
                        status: data_status,
                        _csrf: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response) {

                            console.log(response);
                            $('.dialog-title').text("<?= Yii::t('app', 'Объект опубликован') ?>");
                            $('.dialog-message').text(response.description);
                            $('.save-button').attr('data-dismiss', "modal").addClass('cancel-button');
                            $('.action-buttons').css('display', 'none');

                        }
                    }
                });
            }
        }
        else {
            $.pjax.reload({ container: '#moderation-status-block' });
        }
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
<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\models\Objects;
use yii\widgets\Pjax;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item owner-nav-item-info">
        <?= Html::a(Yii::t('app', 'Панель'), ['/admin']); ?>
    </div>
    <div class="owner-nav-item owner-nav-item-comfort">
        <?= Html::a(Yii::t('app', 'Пользователи'), ['/admin/user']); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-payment">
        <?= Html::a(Yii::t('app', 'Объекты'), ['/admin/object']); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-terms">
        <?= Html::a(Yii::t('app', 'Финансы'), '/admin/finance'); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-crew">
        <?php echo Html::a(Yii::t('app', 'Сотрудники'), '#'); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-feedback">
        <?php echo  Html::a(Yii::t('app', 'Справочники'), '#'); ?>
    </div>
</div>




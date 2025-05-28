<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\models\Objects;
use yii\widgets\Pjax;
$nav_action_class = Yii::$app->controller->action->id;
?>

<div class="owner-nav-cover row">

    <div class="owner-nav-item owner-nav-item-info">
        <?= Html::a(Yii::t('app', 'Информация'), ['view', 'object_id' => $model->id],['class'=>'nav_view']); ?>
    </div>
    <div class="owner-nav-item owner-nav-item-comfort">
        <?= Html::a(Yii::t('app', 'Услуги и особенности'), ['comfort', 'object_id' => $model->id],['class'=>'nav_comfort']); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-payment">
        <?= Html::a(Yii::t('app', 'Оплата'), ['payment', 'object_id' => $model->id],['class'=>'nav_payment']); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-terms">
        <?= Html::a(Yii::t('app', 'Условия'), ['terms', 'object_id' => $model->id],['class'=>'nav_terms']); ?>
    </div>

    <!-- <div class="owner-nav-item owner-nav-item-crew">
        <?php //echo Html::a(Yii::t('app', 'Сотрудники'), ['crew', 'object_id' => $model->id]); ?>
    </div>

    <div class="owner-nav-item owner-nav-item-feedback">
        <?php //echo  Html::a(Yii::t('app', 'Отзывы'), ['feedback', 'object_id' => $model->id]); ?>
    </div> -->
</div>

<?php if (Yii::$app->user->can('admin')) {
    echo $this->render('status_block_admin', ['model' => $model]);
} else {
    echo $this->render('status_block_owner', ['model' => $model]);
}
?>

<style>
.nav_<?=$nav_action_class?> {
    color:#3676BC!important;
}
</style>
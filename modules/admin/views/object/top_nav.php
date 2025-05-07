<?php
use app\models\App;
use yii\helpers\Html;
?>

<div class="top_nav">
    <?php
    if (isset($object_id)) {
        $model->id = $object_id;
    }
    $action = App::getStyle(Yii::$app->controller->action->id);
    $class = "style_" . $action;
    ?>
    <?= Html::a('Отель', ['view', 'object_id' => $model->id], ['class' => "style_hotel top_nav_title top_nav_title_hotel"]); ?>
    <?php //echo Html::a('Доступность и цены', ['prices', 'object_id' => $model->id], ['class' => "style_prices top_nav_title top_nav_title_prices"]); ?>
    <?php //echo Html::a('Бронирования', ['booking', 'object_id' => $model->id], ['class' => 'style_booking top_nav_title top_nav_title_booking']); ?>
    <?php //echo  Html::a('Номера и тарифы', ['room-list', 'object_id' => $model->id], ['class' => 'style_rooms style_room-list top_nav_title top_nav_title_rooms']); ?>
    <?php //echo  Html::a('Финансы', ['finance', 'object_id' => $model->id], ['class' => 'style_finance top_nav_title top_nav_title_finance']); ?>
</div>

<style>
    .style_<?php echo $action ?> {
        border-bottom: 2px solid #3676BC;
    }
</style>
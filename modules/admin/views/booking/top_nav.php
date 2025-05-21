<?php
use app\models\App;
use yii\helpers\Html;
?>

<div class="top_nav">
    <?php
    $action = App::getStyle(Yii::$app->controller->action->id);
    $class = "style_" . $action;
    ?>
    <?= Html::a('Отель', ['/admin/object/view', 'object_id' => $object_id], ['class' => "style_hotel top_nav_title top_nav_title_hotel"]); ?>
    <?php //echo Html::a('Доступность и цены', ['/owner/object/prices', 'object_id' => $object_id], ['class' => "style_prices top_nav_title top_nav_title_prices"]); ?>
    <?php echo Html::a('Бронирования', ['/admin/booking', 'object_id' => $object_id], ['class' => 'style_booking top_nav_title top_nav_title_booking']); ?>
    <?php //echo  Html::a('Номера и тарифы', ['/owner/object/room-list', 'object_id' => $object_id], ['class' => 'style_rooms style_room-list top_nav_title top_nav_title_rooms']); ?>
    <?php //echo  Html::a('Финансы', ['/owner/object/finance', 'object_id' => $object_id], ['class' => 'style_finance top_nav_title top_nav_title_finance']); ?>
</div>

<style>
    .style_booking {
        border-bottom: 2px solid #3676BC;
    }
</style>
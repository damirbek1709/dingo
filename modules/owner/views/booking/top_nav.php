<?php
use app\models\App;
use yii\helpers\Html;
?>

<div class="top_nav">
    <?php
    $action = App::getStyle(Yii::$app->controller->action->id);
    $class = "style_" . $action;
    ?>
    <?= Html::a('Отель', ['/owner/object/view', 'object_id' => $object_id], ['class' => "style_hotel top_nav_title top_nav_title_hotel"]); ?>
    <?php
    if (Yii::$app->user->can('owner'))
     echo Html::a('Доступность и цены', ['/owner/object/prices', 'object_id' => $object_id], ['class' => "style_prices top_nav_title top_nav_title_prices"]); ?>
    <?php echo Html::a('Бронирования', ['/owner/object/booking', 'object_id' => $object_id], ['class' => 'style_booking top_nav_title top_nav_title_booking']); ?>
    <?php echo  Html::a('Номера и тарифы', ['/owner/object/room-list', 'object_id' => $object_id], ['class' => 'style_rooms style_room-list top_nav_title top_nav_title_rooms']); ?>
    <?php echo  Html::a('Финансы', ['/owner/object/finances', 'object_id' => $object_id], ['class' => 'style_finance top_nav_title top_nav_title_finance']); ?>
</div>

<style>
    .style_<?php echo $action ?> {
        border-bottom: 2px solid #3676BC;
    }

    @media (max-width: 768px) {
        .top_nav {
            display: flex;
            padding-bottom: 0;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on iOS */
            scrollbar-width: thin;
            /* Firefox */
            gap: 10px;
            border-bottom: none;
            /* Add some spacing between items */
        }

        /* Hide scrollbar for Webkit browsers */
        .top_nav::-webkit-scrollbar {
            height: 3px;
        }

        .top_nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .top_nav::-webkit-scrollbar-thumb {
            background: #3676BC;
            border-radius: 2px;
        }

        .top_nav a {
            font-size: 16px;
            line-height: 15px;
            letter-spacing: 0px;
            padding-bottom: 15px;
            margin-top: 0;
            white-space: nowrap;
            flex-shrink: 0;
            /* Prevent items from shrinking */
            min-width: fit-content;
            /* Ensure items maintain their natural width */
            padding-left: 8px;
            padding-right: 8px;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topNav = document.querySelector('.top_nav');
        const activeLink = document.querySelector('.style_<?php echo $action ?>');

        if (topNav && activeLink && window.innerWidth <= 768) {
            // Calculate the position to scroll to (align active item to the left)
            const scrollPosition = activeLink.offsetLeft - 10; // 10px offset for better visual alignment

            // Smooth scroll to position
            topNav.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
        }
    });
</script>
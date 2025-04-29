<?php

use app\models\Tariff;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var app\models\TariffSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// $this->title = Yii::t('app', 'Tariffs');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">
    <!-- <h1><?php //echo Html::encode($object_title) ?></h1> -->
    <?php echo $this->render('../../views/object/top_nav', ['model' => $model]); ?>

    <div class="card">
        <div>
            <div style="float:left">
                <div class="button-container">
                    <button
                        class="button button-secondary "><?= Html::a(Yii::t('app', 'Номера'), ['room-list', 'object_id' => $model->id]) ?></button>
                    <button
                        class="button button-primary active"><?= Html::a(Yii::t('app', 'Тарифы'), ['tariff-list', 'object_id' => $model->id]) ?></button>
                </div>
            </div>

            <div style="float:right">
                <?= Html::a(Yii::t('app', '+ Добавить тариф'), ['add-tariff', 'object_id' => $model->id], ['class' => 'add-room-btn']) ?>
            </div>
        </div>

        <?php echo ListView::widget([
            'options' => [
                'class' => 'product-index-cover',
            ],
            'dataProvider' => $dataProvider,
            'viewParams' => ['object_id' => $model->id],
            'itemView' => '_item',
            'summary' => false,
            'itemOptions' => [
                'class' => 'best-seller-block',
            ],
        ]); ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        // When any of the options button is clicked, toggle its corresponding menu
        $('.room-card-options .options-btn').on('click', function (e) {
            e.stopPropagation(); // Prevent click event from bubbling up
            const $menu = $(this).siblings('.options-menu');

            // Close all other open menus
            $('.room-card-options .options-menu').not($menu).slideUp();

            // Toggle the clicked menu
            $menu.stop(true, true).slideToggle();
        });

        // Close all menus if the user clicks anywhere outside the options menu
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.room-card-options').length) {
                $('.room-card-options .options-menu').slideUp();
            }
        });

        $('.dropdown-btn').on('click', function () {
            $(this).parent().toggleClass('active');
        });

        // Close the dropdown if clicking outside of it
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.tariff-dropdown').length) {
                $('.tariff-dropdown').removeClass('active');
            }
        });
    });
</script>
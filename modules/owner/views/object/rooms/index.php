<?php

use app\models\RoomCat;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ListView;
use app\models\Tariff;

/** @var yii\web\View $this */
/** @var app\models\RoomCatSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Список номеров');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Объекты'), 'url' => ['object/view', 'id' => $object_id]];
// $this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['view', 'id' => $object_id]];
// $this->params['breadcrumbs'][] = $this->title;
?>
<link href="<?= Url::base() ?>/modules/owner/assets/css/room.css" rel="stylesheet">
<div class="oblast-update">
    <!-- <h1><?php //echo Html::encode($object_title) ?></h1> -->
    <?php echo $this->render('../top_nav', ['model' => $model, 'object_id' => $object_id]); ?>

    <div class="clear">
        <div class="card">
            <div>
                <div style="float:left">
                    <div class="button-container">
                        <button
                            class="button button-primary active"><?= Html::a(Yii::t('app', 'Номера'), ['room-list', 'object_id' => $object_id]) ?></button>
                        <button
                            class="button button-secondary"><?= Html::a(Yii::t('app', 'Тарифы'), ['tariff-list', 'object_id' => $model->id]) ?></button>
                    </div>
                </div>
                <div style="float:right">
                    <?= Html::a(Yii::t('app', '+ Добавить номер'), ['add-room', 'object_id' => $object_id], ['class' => 'add-room-btn']) ?>
                </div>
            </div>

            <?php

            foreach ($rooms as $key => $val):
                $bind_model = RoomCat::findOne($val['id']);
                $room_id = $val['id'];
                ?>
                <div class="room_list clear">
                    <div class="room-card">
                        <?php
                        if ($bind_model) {
                            echo Html::a(Html::img($bind_model->getImage()->getUrl('120x150'), ['alt' => "Room Image"]), ['/owner/object/room', 'id' => $room_id, 'object_id' => $object_id]);

                        } else {
                            if (array_key_exists('images', $val)) {
                                echo Html::a($val['images']['picture'], ['/owner/object/room', 'id' => $room_id, 'object_id' => $object_id]);
                            }

                        } ?>
                        <div class="room-card-details">
                            <h3><?= Html::a($val['room_title'][0], ['/owner/object/room', 'id' => $room_id, 'object_id' => $object_id]); ?>
                            </h3>
                            <div class="room-info">
                                <span class="room-area">
                                    <span><?= $val['area'] ?> м2</span>
                                </span>

                                <span class="room_guest_amount"><?= $val['guest_amount'] ?>
                                    <?= Yii::t('app', 'взрослых') ?>
                                </span>
                                <?php if (array_key_exists('bed_types', $val) && array_key_exists('0', $val['bed_types'])): ?>
                                    <!-- <span class="room_bed_type"><?php //echo $val['bed_types'][0]['title'] ?>
                                        (<?php //echo $val['bed_types'][0]['quantity'] ?>)
                                    </span> -->
                                <?php endif; ?>
                                <div class="room-card-options">
                                    <button class="options-btn"></button>
                                    <div class="options-menu">
                                        <ul>
                                            <li><span class="room-open-icon"></span>
                                                <?= Html::a(Yii::t('app', 'Открыть'), ['room', 'id' => $val['id'], 'object_id' => $object_id], ['class' => '']) ?>
                                            </li>
                                            <li class="room-delete-btn"><span class="room-delete-icon"></span>
                                                <?= Html::a(Yii::t('app', 'Удалить'), ['delete-room', 'id' => $val['id'], 'object_id' => $object_id], ['class' => '']) ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <div class="bed-info"><?php //$model['bed_types'] ?></div>

                            <div class="tariff-dropdown">
                                <button class="dropdown-btn"><?= Yii::t('app', 'Привязанные тарифы') ?> <span
                                        class="tariff-label"></span></button>
                                <div class="dropdown-menu">
                                    <?php
                                    $objectId = (int) $val['id'];
                                    $tariffList = Tariff::find()->where(['object_id' => $object_id])->all();
                                    foreach ($tariffList as $tariff):
                                        $checked = "";
                                        if (array_key_exists('tariff', $val)) {
                                            if ($tariff->isTariffBinded($val['tariff'])) {
                                                $checked = "checked";
                                            }
                                        }
                                        ?>
                                        <label>
                                            <input type="checkbox" <?= $checked; ?> name="tariff" value="<?= $tariff->id ?>"
                                                object_id="<?= $object_id ?>" room_id="<?= $val['id'] ?>" class="tariff-bind">
                                            <?php echo $tariff->title; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
</script>



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


        $('.tariff-bind').on('change', function () {
            var tariffId = $(this).val();  // Get the tariff ID
            var room_id = $(this).attr('room_id');
            var isChecked = $(this).prop('checked');  // Check if the checkbox is checked
            var object_id = $(this).attr('object_id');  // Check if the checkbox is checked

            // Send AJAX request to the backend
            $.ajax({
                url: "<?= Yii::$app->urlManager->createUrl('/owner/tariff/bind-tariff') ?>", // Your action URL
                type: 'POST',
                data: {
                    tariff_id: tariffId,  // Send the tariff ID
                    checked: isChecked,
                    room_id: room_id,   // Send the checked state
                    object_id: object_id,   // Send the checked state
                    _csrf: $('meta[name="csrf-token"]').attr('content')  // CSRF token for security (if needed)
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Tariff successfully updated');
                    } else {
                        console.log('Failed to update tariff');
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX error: ' + status + ' ' + error);
                }
            });
        });
    });

</script>
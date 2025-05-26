<?php

use app\models\App;
use app\models\RoomCat;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$cancellation_title = $model->getCancellationTitle($model->cancellation);
$meal_title = $model->getMealTitle($model->meal_type);
?>
<div class="room_list clear">
    <div>
        <div class="room-card">
            <div class="room-card-details">
                <h3><?= $model->title; ?></h3>
                <div class="room-info">
                    <span class="room-area <?= $cancellation_title['class'] ?>">
                        <span><?php echo $cancellation_title['label']; ?></span>
                    </span>

                    <span class="room_guest_amount <?= $meal_title['class']; ?>">
                        <?php echo $meal_title['label']; ?>
                    </span>

                    <div class="room-card-options">
                        <button class="options-btn"></button>
                        <div class="options-menu">
                            <ul>
                                <li><span class="room-open-icon"></span>
                                    <?= Html::a(Yii::t('app', 'Редактировать'), ['/owner/object/edit-tariff', 'id' => $model->id, 'object_id' => $object_id], ['class' => '']) ?>
                                </li>
                                <li class="room-delete-btn"><span class="room-delete-icon"></span>
                                    <?= Html::a(Yii::t('app', 'Удалить'), ['/owner/tariff/delete', 'id' => $model->id, 'object_id' => $object_id], ['class' => '']) ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <span class="tariff-dropdown">
            <button class="dropdown-btn"><?= Yii::t('app', 'Привязанные номера') ?> <span
                    class="tariff-label"></span></button>
            <div class="dropdown-menu">
                <?php
                if ($room_list_session) {
                    $room_list = $room_list_session;
                } else {
                    $client = Yii::$app->meili->connect();
                    $object = $client->index('object')->getDocument($object_id);
                    $room_list = $object['rooms'] ?? $room_list_session;
                }


                foreach ($room_list as $room):
                    $checked = "";
                    if (array_key_exists('tariff', $room)) {
                        foreach ($room['tariff'] as $item) {
                            if ($item['id'] == $model->id) {
                                $checked = "checked";
                            }
                        }
                    }
                    ?>
                    <label>
                        <input type="checkbox" <?php echo $checked; ?> name="room" value="<?php echo $room['id'] ?>"
                            object_id="<?php echo $object_id ?>" tariff_id="<?= $model->id ?>"
                            room_id="<?php echo $room['id'] ?>" class="room-bind">
                        <?php echo $room['room_title'][0]; ?>
                    </label>

                <?php endforeach; ?>
            </div>
        </span>
    </div>
</div>
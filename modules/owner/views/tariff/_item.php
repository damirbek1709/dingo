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

                    <span class="tariff-dropdown">
                        <button class="dropdown-btn"><?= Yii::t('app', 'Привязанные номера') ?> <span
                                class="tariff-label"></span></button>
                        <div class="dropdown-menu">
                            <?php
                            //$objectId = (int) $val['id'];
                            
                            $client = Yii::$app->meili->connect();
                            $object = $client->index('object')->getDocument($object_id);
                            $room_list = $object['rooms'] ?? []; 
                            //  foreach ($room_list as $tariff):
                            //      $checked = "";
                            //      if (array_key_exists('tariff', $val)) {
                            //          if ($tariff->isTariffBinded($val['tariff'])) {
                            //              $checked = "checked";
                            //          }
                            //      }

                            foreach ($room_list as $room):?>
                             <label> 
                                <input type="checkbox" <?php //echo $checked; ?> name="tariff" value="<?php echo $room['id'] ?>"
                                         object_id="<?php echo $object_id ?>" room_id="<?php echo $model->id ?>" class="tariff-bind">
                                     <?php echo $room['room_title']; ?>
                            </label>
                            
                            <?php endforeach; ?>
                        </div>
                    </span>

                    <div class="room-card-options">
                        <button class="options-btn"></button>
                        <div class="options-menu">
                            <ul>
                                <li><span class="room-open-icon"></span>
                                    <?= Html::a(Yii::t('app', 'Редактировать'), ['/owner/tariff/update', 'id' => $model->id, 'object_id' => $object_id], ['class' => '']) ?>
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
    </div>
</div>
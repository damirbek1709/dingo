<?php

use app\models\Comfort;
use app\models\Objects;
use app\models\RoomComfort;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Удобства номеров');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', ''), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">
    <?php $form = ActiveForm::begin();
    $list_comfort = Objects::roomComfortList();
    ?>
    <div class="col-md-12">
        <div class="row">

            <div class="col-md-3">
                <?php echo $this->render('room_nav', ['room_id' => $room_id, 'object_id' => $object_id]); ?>
            </div>

            <div class="col-md-9">
                <h1><?= Html::encode($this->title) ?></h1>
                <?php
                function isComfortChecked($comfortData, $comfortId): bool
                {
                    if (empty($comfortData) || !is_array($comfortData)) {
                        return false;
                    }

                    $comfortId = (int) $comfortId;
                    $found = false;

                    foreach ($comfortData as $categoryId => $comforts) {
                        if (array_key_exists($comfortId, $comforts)) {
                            $found = true;
                            break;
                        }
                    }

                    return $found;
                }
                ?>

                <?php foreach ($list_comfort as $categoryId => $comforts):
                    $category_name = RoomComfort::getComfortCategoryTitle(id: $categoryId);
                    ?>
                    <fieldset>
                        <legend><strong><?= Html::encode($categoryNames[$categoryId] ?? $category_name) ?></strong></legend>
                        <div class="comfort_list_grid">
                            <?php foreach ($comforts as $comfort): ?>
                                <div>
                                    <?= Html::checkbox(
                                        "comforts[]",
                                        isComfortChecked($room['comfort'] ?? [], $comfort->id),
                                        ['value' => $comfort->id]
                                    ) ?>
                                    <?= Html::encode($comfort->title) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <br>
                <?php endforeach; ?>

                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
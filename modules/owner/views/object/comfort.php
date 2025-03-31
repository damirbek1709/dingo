<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Услуги и особенности');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Услуги и особенности');
?>
<div class="oblast-update">
    <?php $form = ActiveForm::begin();
    $list_comfort = Objects::сomfortList();
    ?>
    <div class="col-md-12">
        <div class="row">

            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9">
                <h1><?= Html::encode($this->title) ?></h1>
                <?php foreach ($list_comfort as $categoryId => $comforts):
                    $category_name = Comfort::getComfortCategoryTitle(id: $categoryId);
                    $selectedComforts = $model->comfort_list[$categoryId] ?? []; // Get selected comforts for this category
                    ?>
                    <fieldset>
                        <legend><strong><?= Html::encode($categoryNames[$categoryId] ?? $category_name) ?></strong></legend>
                        <div class="comfort_list_grid">
                            <?php foreach ($comforts as $comfort): ?>
                                <div>
                                    <?= Html::checkbox("comforts[]", isset($selectedComforts[$comfort->id]), ['value' => $comfort->id]) ?>
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
    </div>
    <?php ActiveForm::end(); ?>

</div>
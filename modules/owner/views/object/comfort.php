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
                <?= $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9">
                <h1><?= Html::encode($this->title) ?></h1>
                <p><?=Yii::t('app','Выберите 5 и более удобств в вашем объекте размещения.')?></p>
                <?php foreach ($list_comfort as $categoryId => $comforts):
                    $category_name = Comfort::getComfortCategoryTitle(id: $categoryId);
                    $selectedComforts = $model->comfort_list[$categoryId] ?? []; // Get selected comforts for this category
                    ?>
                    <fieldset>
                        <legend><strong><?= Html::encode($categoryNames[$categoryId] ?? $category_name) ?></strong></legend>
                        <div class="comfort_list_grid">
                            <?php foreach ($comforts as $comfort): ?>
                                <div>
                                    <?= Html::checkbox("comforts[{$categoryId}][{$comfort->id}][selected]", isset($selectedComforts[$comfort->id]), ['value' => 1]) ?>
                                    <?= Html::encode($comfort->title) ?>
                                    ( 
                                    <?= Html::checkbox("comforts[{$categoryId}][{$comfort->id}][is_paid]", isset($selectedComforts[$comfort->id]['is_paid']) && $selectedComforts[$comfort->id]['is_paid'], ['value' => 1]) ?>
                                    <label style="font-weight:normal">Платная услуга</label>
                                    )
                                    
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
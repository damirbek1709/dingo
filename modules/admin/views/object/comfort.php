<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

// $this->title = Yii::t('app', 'Услуги и особенности');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Услуги и особенности');
?>
<div class="oblast-update">
<?php echo $this->render('top_nav', ['model' => $model]); ?>
    <?php $form = ActiveForm::begin();
    $list_comfort = Objects::сomfortList();
    ?>
    <div class="col-md-12">
        <div class="row">

            <div class="col-md-3">
                <?= $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <h3><?= Html::encode($model->name[0]) ?></h3>
                    <p><?= Yii::t('app', 'Выберите 5 и более удобств в вашем объекте размещения.') ?></p>
                    <?php foreach ($list_comfort as $categoryId => $comforts):
                        $category_name = Comfort::getComfortCategoryTitle(id: $categoryId);
                        $selectedComforts = $model->comfort_list[$categoryId] ?? []; // Get selected comforts for this category ?>
                        <fieldset>
                            <legend><?= Html::encode($categoryNames[$categoryId] ?? $category_name) ?></legend>
                            <div class="comfort_list_grid">
                                <?php foreach ($comforts as $comfort): ?>
                                    <div class="comfort-item">
                                        <?= Html::checkbox("comforts[{$categoryId}][{$comfort->id}][selected]", isset($selectedComforts[$comfort->id]), ['value' => 1, 'id' => "comfort-{$categoryId}-{$comfort->id}"]) ?>
                                        <label for="comfort-<?= $categoryId ?>-<?= $comfort->id ?>"><?= Html::encode($comfort->title) ?></label>

                                        <div class="toggle-switch-container">
                                            <label class="toggle-switch">
                                                <input type="checkbox"
                                                    name="comforts[<?= $categoryId ?>][<?= $comfort->id ?>][is_paid]" value="1"
                                                    <?= (isset($selectedComforts[$comfort->id]['is_paid']) && $selectedComforts[$comfort->id]['is_paid']) ? 'checked' : '' ?>>
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="toggle-label">платно</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <br>
                    <?php endforeach; ?>
                    <div class="form-group">
                        <?= Html::submitButton('Сохранить', ['class' => 'save-button']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<style>
    .comfort-item {
        display: grid;
        grid-template-columns: 30px 6fr 6fr;
        margin-bottom: 15px;
        align-items: center;
    }

    .toggle-switch-container {
        display: flex;
        align-items: center;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 35px;
        height: 20px;
        margin-right: 10px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 10px;
        width: 10px;
        left: 4px;
        bottom: 5px;
        background-color: white;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #3676BC;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #3676BC;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }

    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .toggle-label {
        font-weight: normal;
        margin-left: 10px;
    }

    .comfort_list_grid label {
        font-weight: normal;
        margin: 0;
    }

    .comfort-item input[type="checkbox"] {
        margin: 0;
        width: 18px;
        height: 18px;
    }

    .card legend {
        border-bottom: none;
        font-weight: 500;
        font-size: 20px;
    }

    .card h3 {
        margin-top: 0;
    }
</style>
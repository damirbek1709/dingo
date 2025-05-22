<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

// $this->title = Yii::t('app', 'Условия');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Условия');
?>
<div class="oblast-update">
    <?php echo $this->render('top_nav', ['model' => $model, 'object_id' => $model->id]); ?>
    <?php
    $form = ActiveForm::begin();
    $meal_list = Objects::mealList();
    ?>

    <div class="col-md-12 terms_container">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <h2 class="general_title"><?= Yii::t('app', 'Условия'); ?></h2>
                    <!-- Early & Late Check-in -->
                    <h4 class="minor_title"><?= Yii::t('app', 'Ранний заезд, Поздний заезд'); ?></h4>
                    <?= Html::checkbox('early_check_in', $model->early_check_in ? true : false, ['label' => 'Доступен ранний заезд']) ?>
                    <div class="clear"></div>
                    <?= Html::checkbox('late_check_in', $model->late_check_in ? true : false, ['label' => 'Доступен поздний выезд']) ?>

                    <div id="meal-terms-container">
                        <h4 class="minor_title"><?= Yii::t('app', 'Питание с оплатой на месте'); ?></h4>
                        <div class="toggle-switch-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="meal_purchaise" value="1" <?= $model->meal_purchaise ? 'checked' : '' ?> class="toggle-switch-input">
                                <span class="slider round"></span>
                            </label>
                            <span class="toggle-label"><?= Yii::t('app', 'Доступно с оплатой на месте') ?></span>
                        </div>

                        <?php if (!empty($model->meal_terms)): ?>
                            <?php foreach ($model->meal_terms as $index => $meal): ?>
                                <div class="meal-row">

                                    <div class="select-box-double">
                                        <label><?= Yii::t('app', 'Тип питания') ?></label>
                                        <?= Html::dropDownList("meal_terms[$index][meal_type]", $meal['meal_type'] ?? '', $meal_list, ['class' => 'form-control', 'prompt' => Yii::t('app', 'Выберите тип питания')]) ?>
                                    </div>

                                    <div class="select-box-double">
                                        <label><?= Yii::t('app', 'Стоимость') ?></label>
                                        <?= Html::input('text', "meal_terms[$index][meal_cost]", $meal['meal_cost'] ?? '', ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Укажите стоимость питание')]) ?>
                                    </div>

                                    <button type="button" class="btn remove-meal"></button>

                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Show first meal row if no meals exist (new record case) -->

                            <div class="meal-row">
                                <div class="select-box-double">
                                    <label><?= Yii::t('app', 'Тип питания') ?></label>
                                    <?= Html::dropDownList("meal_terms[0][meal_type]", '', $meal_list, ['class' => 'form-control', 'prompt' => Yii::t('app', 'Выберите тип питания')]) ?>
                                </div>

                                <div class="select-box-double">
                                    <label><?= Yii::t('app', 'Стоимость') ?></label>
                                    <?= Html::input('text', "meal_terms[0][meal_cost]", '', ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Укажите стоимость питание')]) ?>
                                </div>

                                <button type="button" class="btn remove-meal"></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Meal Terms -->

                    <!-- Button to add new meal type -->
                    <button type="button" id="add-meal" class="btn btn-terms"><span class="add-meal-plus">&#43;</span>
                        <?= Yii::t('app', 'Добавить тип питания') ?></button>

                    <div class="terms_section"><!-- Internet & Animals Allowed -->
                        <h4 class="minor_title"><?php echo Yii::t('app', 'Интернет в общественных местах'); ?></h4>
                        <?= Html::checkbox('internet_public', $model->internet_public ? true : false, ['label' => 'Интернет доступен']) ?>
                    </div>

                    <div class="terms_section">
                        <h4 class="minor_title"><?= Yii::t('app', 'Политика проживания с животными') ?></h4>
                        <?= Html::checkbox('animals_allowed', $model->animals_allowed ? true : false, ['label' => 'Разрешено проживание с животными']) ?>
                    </div>

                    <div class="terms_section">
                        <h4><?= Yii::t('app', 'Младенцы'); ?></h4>
                        <div class="hint"><?= Yii::t('app', 'Размещаются с родителями бесплатно без места'); ?></div>
                        <div class="increment-input">
                            <button type="button" class="decrement">-</button>
                            <?= Html::input('text', 'children', $model->children ? $model->children : 0, [
                                'class' => 'form-control children-count',
                                'readonly' => true,
                                'label' => 'Грудные дети до'
                            ]); ?>
                            <button type="button" class="increment">+</button>
                        </div>
                        <div class="clear"></div>
                    </div>



                    <!-- Submit Button -->
                    <div class="form-group">
                        <?= Html::submitButton('Сохранить', ['class' => 'save-button']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$mealListJson = json_encode($meal_list);
$script = <<<JS
$(document).ready(function() {
    $('.increment').click(function() {
        let input = $(this).siblings('.children-count');
        let value = parseInt(input.val());
        if(value <= 17)
        {
            input.val(value + 1);
        }
    });

    $('.decrement').click(function() {
        let input = $(this).siblings('.children-count');
        let value = parseInt(input.val());
        if (value > 0) {
            input.val(value - 1);
        }
    });

    let mealIndex = $(".meal-row").length; 
    let mealList = $mealListJson; // Convert PHP array to JS object

    $("#add-meal").click(function() {
        let mealOptions = '';
        $.each(mealList, function(key, value) {
            mealOptions += `<option value="\${key}">\${value}</option>`;
        });

        let newMealRow = `
                <div class="meal-row">
                    <div class="select-box-double">
                        <label>Тип питания</label>
                        <select name="meal_terms[\${mealIndex}][meal_type]" class="form-control" prompt="Выберите тип питания">
                            \${mealOptions}
                        </select>
                    </div>

                    <div class="select-box-double">
                        <label>Стоимость</label>
                        <input type="text" name="meal_terms[\${mealIndex}][meal_cost]" class="form-control" placeholder="Укажите стоимость питание">
                    </div>

                    <button type="button" class="btn remove-meal"></button>
                </div>
            </div>
        `;

        $("#meal-terms-container").append(newMealRow);
        mealIndex++;
    });

    $(document).on("click", ".remove-meal", function() {
        $(this).closest(".meal-row").remove();
    });
});
JS;
$this->registerJs($script);
?>

<style>
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
        margin: -15px 10px 0 0;
    }
</style>
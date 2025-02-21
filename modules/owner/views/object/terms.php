<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Условия');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Условия');
?>
<div class="oblast-update">
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
                <h2>Условия</h2>

                <!-- Early & Late Check-in -->
                <h4><?= Yii::t('app', 'Ранний заезд, Поздний заезд'); ?></h4>
                <?= Html::checkbox('early_check_in', $model->early_check_in ? true : false, ['label' => 'Доступен ранний заезд']) ?>
                <div class="clear"></div>
                <?= Html::checkbox('late_check_in', $model->late_check_in ? true : false, ['label' => 'Доступен поздний выезд']) ?>



                <div id="meal-terms-container">
                    <h4>Питание с оплатой на месте</h4>
                    <div class="terms_section"><!-- Internet & Animals Allowed -->
                        <?= Html::checkbox('meal_purchaise', $model->meal_purchaise ? true : false, ['label' => 'Доступно с оплатой на месте']) ?>
                    </div>
                    <?php if (!empty($model->meal_terms)): ?>
                        <?php foreach ($model->meal_terms as $index => $meal): ?>
                            <div class="row">
                                <div class="meal-row col-md-12">

                                    <div class="col-md-5 select-box-double">
                                        <label>Тип питания</label>
                                        <?= Html::dropDownList("meal_terms[$index][meal_type]", $meal['meal_type'] ?? '', $meal_list, ['class' => 'form-control']) ?>
                                    </div>

                                    <div class="col-md-5 select-box-double">
                                        <label>Стоимость</label>
                                        <?= Html::input('text', "meal_terms[$index][meal_cost]", $meal['meal_cost'] ?? '', ['class' => 'form-control']) ?>
                                    </div>


                                    <div class="col-md-1">
                                        <button type="button" class="btn remove-meal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                                class="bi bi-trash" viewBox="0 0 16 16">
                                                <path
                                                    d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                                <path
                                                    d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Show first meal row if no meals exist (new record case) -->
                        <div class="row">
                            <div class="meal-row col-md-12 row">
                                <div class="col-md-5 select-box-double">
                                    <label>Тип питания</label>
                                    <?= Html::dropDownList("meal_terms[0][meal_type]", '', $meal_list, ['class' => 'form-control']) ?>
                                </div>

                                <div class="col-md-5 select-box-double">
                                    <label>Стоимость</label>
                                    <?= Html::input('text', "meal_terms[0][meal_cost]", '', ['class' => 'form-control']) ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn remove-meal">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                            class="bi bi-trash" viewBox="0 0 16 16">
                                            <path
                                                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                            <path
                                                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Meal Terms -->

                <!-- Button to add new meal type -->
                <button type="button" id="add-meal" class="btn btn-terms"><span style='font-size:20px;'>&#43;</span>
                    Добавить тип питания</button>

                <div class="terms_section"><!-- Internet & Animals Allowed -->
                    <h4><?php echo Yii::t('app', 'Интернет в общественных местах'); ?></h4>
                    <?= Html::checkbox('internet_public', $model->internet_public ? true : false, ['label' => 'Интернет доступен']) ?>
                </div>

                <div class="terms_section">
                    <h4>Политика проживания с животными</h4>
                    <?= Html::checkbox('animals_allowed', $model->animals_allowed ? true : false, ['label' => 'Разрешено проживание с животными']) ?>
                </div>


                <!-- Submit Button -->
                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
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
    let mealIndex = $(".meal-row").length; 
    let mealList = $mealListJson; // Convert PHP array to JS object

    $("#add-meal").click(function() {
        let mealOptions = '';
        $.each(mealList, function(key, value) {
            mealOptions += `<option value="\${key}">\${value}</option>`;
        });

        let newMealRow = `
            <div class="row">
                <div class="meal-row col-md-12">
                    <div class="col-md-5 select-box-double">
                        <label>Тип питания</label>
                        <select name="meal_terms[\${mealIndex}][meal_type]" class="form-control">
                            \${mealOptions}
                        </select>
                    </div>

                    <div class="col-md-5 select-box-double">
                        <label>Стоимость</label>
                        <input type="text" name="meal_terms[\${mealIndex}][meal_cost]" class="form-control">
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn remove-meal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                            </svg>
                        </button>
                    </div>
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
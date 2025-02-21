<?php

use app\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-form">
    <?php $form = ActiveForm::begin();
    $alter_cats = $model->getMeiliCats();
    ?>
    <?= $form->field($model, 'parent_id')->hiddenInput()->label(false) ?>
    <div>
        <?php
        if (!$model->isNewRecord):
            $counter = 1;
            $display = "block";
            $parents = Category::parentRecursive($model->id);
            unset($parents[19]);

            //echo "<pre>";print_r($parents);echo "<pre>";die();
        
            foreach ($parents as $key => $val) {
                $attribute = "cat_depth_$counter";
                $model->$attribute = [$key];
                echo $form->field($model, $attribute)->widget(Select2::className(), [
                    'data' => Category::parentRecursiveMeili($key),
                    'options' => [
                        'encode' => false,
                        'class' => 'cats-box',
                        'placeholder' => 'Выберите родительскую категорию ...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'class' => "cats-box cat-depth-$counter"
                    ],
                ])->label("Родительская категория {$counter}");
                $counter++;
            } ?>
        </div>
    <? else:
            $display = "none";
            ?>
        <div>
            <?= $form->field($model, 'cat_depth_1')->widget(Select2::className(), [
                'data' => $alter_cats,
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box',

                ],
            ])->label('Родительская категория'); ?>

            <?= $form->field($model, 'cat_depth_2')->widget(Select2::className(), [
                'data' => [],
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box cat-depth-hidden',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box cat-depth-2 cat-depth-hidden',

                ],
            ])->label('Подкатегория 1'); ?>

            <?= $form->field($model, 'cat_depth_3')->widget(Select2::className(), [
                'data' => [],
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box cat-depth-hidden',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box cat-depth-3 cat-depth-hidden',

                ],
            ])->label('Подкатегория 2'); ?>

            <?= $form->field($model, 'cat_depth_4')->widget(Select2::className(), [
                'data' => [],
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box cat-depth-4 cat-depth-hidden',

                ],
            ])->label('Подкатегория 3'); ?>

            <?= $form->field($model, 'cat_depth_5')->widget(Select2::className(), [
                'data' => [],
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box cat-depth-5 cat-depth-hidden',

                ],
            ])->label('Подкатегория 4'); ?>

            <?= $form->field($model, 'cat_depth_6')->widget(Select2::className(), [
                'data' => [],
                //'data' =>Category::getAlternativesList(),
                'options' => [
                    'encode' => false,
                    'class' => 'cats-box',
                    'placeholder' => 'Выберите родительскую категорию ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'class' => 'cats-box cat-depth-6 cat-depth-hidden',
                ],
            ])->label('Подкатегория 5'); ?>
        </div>
    <?php endif; ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_ky')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->widget(Select2::className(), [
        'data' => Category::getTypeOptions(),
    ]) ?>

    <div class="form-group">
        <?php foreach ($model->getImages() as $image): ?>
            <?= Html::img($image->getUrl('300x'), ['class' => 'img-thumbnail img-rounded']) ?>
        <?php endforeach; ?>
    </div>

    <?= $form->field($model, 'images')->fileInput(['accept' => 'image/*']); ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'vip_min_price')->textInput() ?>
    <?= $form->field($model, 'vip_max_price')->textInput() ?>

    <?= $form->field($model, 'is_alternative')->checkbox(['disabled' => !$model->isNewRecord]) ?>

    <?= $form->field($model, 'cart_off')->checkbox() ?>
    <?= $form->field($model, 'is_moderation_needed')->checkbox() ?>

    <?= $form->field($model, 'is_post_count_visible')->checkbox() ?>

    <?= $form->field($model, 'post_limit')->textInput(['maxlength' => true]) ?>

    <?php
    if (!$model->isNewRecord && $model->is_alternative === Category::IS_NOT_ALTERNATIVE) {
        echo $form->field($model, 'alternative_category_ids')->widget(Select2::className(), [
            //'data' => Category::getAlternativesList(),
            'data' => $model->getAlterCats(),
            'options' => [
                'encode' => false,
                'multiple' => true,
                'placeholder' => 'Выберите категории ...',
            ],
        ]);
    }
    ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    $(document).ready(function () {
        $('.cats-box').on("change", function (e) {
            if ($(this).val() !== '') {
                var id = $(this).val();
                var thisOne = $(this);
                var nextCatBlock = thisOne.parent('.form-group').next();

                $.ajax({
                    method: "POST",
                    url: "<?= Yii::$app->urlManager->createUrl('/admin/category/child-category') ?>",
                    data: {
                        id: id,
                    },
                    success: function (res) {
                        if (res != "false") {
                            nextAllBlocks = nextCatBlock.nextAll();
                            nextAllBlocks.each(function (index, element) {
                                jQuery(element).find('select').empty();
                            });
                            nextAllBlocks.css('display', 'none');

                            var data = JSON.parse(res);
                            var select2 = nextCatBlock.find('select');
                            select2.empty();
                            select2.append('<option value="" selected="selected">Выберите категорию</option>');
                            $.each(data, function (key, value) {
                                select2.append('<option value="' + key + '">' + value + '</option>');
                            });
                            $('#category-parent_id').val(id);
                            nextCatBlock.css('display', 'block');
                        }
                    },
                });
            } else {
                $('#category-parent_id').val('');
            }
        });
    });
</script>

<style>
    .field-category-cat_depth_2,
    .field-category-cat_depth_3,
    .field-category-cat_depth_4,
    .field-category-cat_depth_5,
    .field-category-cat_depth_6,
    .field-category-cat_depth_7 {
        display:
            <?= $display; ?>
        ;
    }
</style>
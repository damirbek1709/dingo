<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */
/** @var yii\widgets\ActiveForm $form */


$this->title = Yii::t('app', 'Редактировать пользователя');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">
    <div class="object-admin-grid">
        <div class="col-md-12">
            <?php echo $this->render('../object/nav-left'); ?>
        </div>

        <div class="col-md-12">
            <div class="card">
                <h1 class="general_title"><?= Html::encode($this->title) ?></h1>
                <?php $form = ActiveForm::begin([
                    // 'enableClientValidation' => true,
                    // 'enableAjaxValidation' => true,
                    
                ]); ?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'fee_percent')->textInput(['maxlength' => true, 'class' => 'form-input']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'save-button']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
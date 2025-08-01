<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Способы оплаты в апартаментах');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Услуги и особенности');
?>
<div class="oblast-update">
    <?php echo $this->render('top_nav', ['model' => $model,'object_id'=>$model->id]); ?>
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9 mob_card_cover">
                <div class="card">
                    <h1 class="general_title"><?= Html::encode($this->title) ?></h1>
                    <div class="payment-methods-container">
                        <?php foreach ($paymentTypes as $payment): ?>
                            <div class="payment-option">
                                <div class="payment-logo payment type_<?= $payment['id'] ?>"></div>
                                <div class="payment-title">
                                    <?= Html::encode($payment['title']) ?>
                                </div>
                                <div class="checkbox-container">
                                    <?= Html::checkbox('payment_type[]', in_array($payment['id'], $selectedPayments), [
                                        'value' => $payment['id'],
                                        'class' => 'payment-checkbox',
                                        'label' => false
                                    ]) ?>

                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="form-group margin-15">
                            <?= Html::submitButton('Сохранить', ['class' => 'save-button']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<style>

</style>
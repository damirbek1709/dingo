<?php

use app\models\Comfort;
use app\models\Objects;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Способы оплаты');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name[0], 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Услуги и особенности');
?>
<div class="oblast-update">
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <?php echo $this->render('nav', ['model' => $model]); ?>
            </div>

            <div class="col-md-9">
                <h1><?= Html::encode($this->title) ?></h1>
                <?php foreach ($paymentTypes as $payment): ?>
                    <div>
                        <?= Html::checkbox('payment_type[]', in_array($payment['id'], $selectedPayments), [
                            'value' => $payment['id']
                        ]) ?>
                        <?= Html::encode($payment['title']) ?>
                    </div>
                <?php endforeach; ?>

                <div class="form-group margin-15">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
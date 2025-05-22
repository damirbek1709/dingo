<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

// $index = 0;
// $name_arr = $model->name;
// $name = $name_arr[$index];

$this->title = Yii::t('app', 'Update Object');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="oblast-update">
    <?php echo $this->render('top_nav', ['model' => $model,'object_id'=>$model->id]); ?>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <div class="col-md-12">
                    <div class="row">
                        <?php echo $this->render('nav', ['model' => $model]); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <?= $this->render('_form_update', [
                        'model' => $model,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
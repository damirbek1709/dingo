<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Oblast $model */

$this->title = Yii::t('app', 'Создание объекта');
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Objects'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Создать объект');
?>
<div class="oblast-update">
    <?php //echo $this->render('top_nav', ['model' => $model]); ?>
    <div class="col-md-12">
        <div class="row">
            <div class="card">
                <h1><?= Html::encode($this->title) ?></h1>
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
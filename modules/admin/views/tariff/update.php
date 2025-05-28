<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = Yii::t('app', 'Редактировать тариф: {name}', [
    'name' => $model->title,
]);
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tariffs'), 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>

<div class="oblast-update">
    <?php echo $this->render('../../views/object/top_nav', ['model' => $model, 'object_id' => $object_id]); ?>
    <div class="card">
        <div class="col-md-6">
            <h3><?= Yii::t('app', 'Редактировать тариф') ?></h3>
            <?= $this->render('_form', [
                'model' => $model,
                'object_id' => $object_id
            ]) ?>
        </div>
    </div>
</div>
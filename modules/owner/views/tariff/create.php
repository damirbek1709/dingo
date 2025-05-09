<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tariff $model */

$this->title = Yii::t('app', 'Создание нового тарифа');
// $this->params['breadcrumbs'][] = ['label' => $object_title, 'url' => ['view', 'id' => $object_id]];
// $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Номера и тарифы'), 'url' => ['room-list', 'id' => $object_id]];
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="oblast-update">
    <?php echo $this->render('../../views/object/top_nav', ['model' => $model, 'object_id' => $object_id]); ?>
    <div class="card">
        <div class="col-md-6">
            <h3><?= Html::encode($this->title) ?></h3>
            <?= $this->render('_form', [
                'model' => $model,
                'object_id' => $object_id
            ]) ?>
        </div>
    </div>
</div>
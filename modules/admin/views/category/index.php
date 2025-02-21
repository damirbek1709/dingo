<?php

use yii\helpers\Html;
use app\components\Nestable;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $query yii\db\Query */

$this->title = 'Категории';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить категорию', ['create'], ['class' => 'btn btn-success']) ?>
        <span id="nestable-menu">
            <button class="btn btn-default" type="button" data-action="expand-all">Развернуть</button>
            <button class="btn btn-default" type="button" data-action="collapse-all">Свернуть</button>
        </span>
    </p>

    <?= Nestable::widget([
        'type' => Nestable::TYPE_WITH_HANDLE,
        'query' => $query,
        'modelOptions' => [
            'name' => 'title',
        ],
        'pluginEvents' => [
            'change' => 'function(e) {}',
        ],
        'pluginOptions' => [
            'maxDepth' => 100,
        ],
        'update' => Url::to(['update']),
        'delete' => Url::to(['delete']),
        'viewItem' => Url::to(['view']),
    ]); ?>

</div>

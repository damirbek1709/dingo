<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DirectorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Справочники';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="directory-index">

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'title',
                'enableSorting' => false,
            ],
            /* [
                'attribute' => 'title_ky',
                'enableSorting' => false,
            ], */

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
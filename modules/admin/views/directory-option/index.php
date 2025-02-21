<?php

use yii\widgets\Pjax;
use yii\grid\GridView;
use app\models\Directory;
use app\models\DirectoryOption;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DirectoryOptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Элементы справочников';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="directory-option-index">

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'directory_id',
                'value' => function (DirectoryOption $data) {
                    return $data->directory->title;
                },
                'filter' => Directory::getList(),
            ],
            'value',
            'value_ky',
            'position',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
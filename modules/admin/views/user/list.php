<?php

use app\models\user\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\OblastSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Пользователи');
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
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'summary' => false,
                    'columns' => [
                        'name',
                        'email',
                        'phone',
                        [
                            'class' => ActionColumn::className(),
                            'template' => '{switch}{update}{delete}',
                            'buttons' => [
                                'switch' => function ($url, $model) {

                                                    return Html::a('<span class="glyphicon glyphicon-user"></span>', ['/user/admin/switch', 'id' => $model->id], [
                                                        'title' => Yii::t('user', 'Become this user'),
                                                        'data-confirm' => Yii::t('user', 'Are you sure you want to switch to this user for the rest of this Session?'),
                                                        'data-method' => 'POST',
                                                    ]);

                                                }
                            ],
                            'urlCreator' => function ($action, User $model, $key, $index, $column) {
                                                return Url::toRoute([$action, 'id' => $model->id]);
                                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
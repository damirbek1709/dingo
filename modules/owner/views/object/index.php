<?php
use yii\helpers\Html;
?>
<p>
    <?php echo Html::a('Добавить объект', ['create'], ['class' => 'btn btn-primary']) ?>
</p>
<?php
echo \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'email',
        [
            'attribute' => 'name',
            'value' => function ($model) {
                echo print_r($model);die();
                $name = "";
                foreach ($model as $val) {
                    $name = " " . $val;
                }
                return $name;
            }
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'urlCreator' => function ($action, $model, $key, $index) {
                return \yii\helpers\Url::to([$action, 'id' => $model['id']]);
            },
        ],
        //['class' => 'yii\grid\ActionColumn'], // Optional actions column
    ],
]);
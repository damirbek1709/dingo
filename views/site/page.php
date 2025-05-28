<?php

/** @var yii\web\View $this */

use app\models\Page;
use yii\helpers\Html;

$model = Page::findOne($id);
$this->title = $model->title;
?>
<div class="oblast-update">
    <div class="card">
    <h3 class=" general_title"><?= Html::encode($this->title) ?></h3>
        <?php
        echo $model->text;
        ?>
    </div>
</div>
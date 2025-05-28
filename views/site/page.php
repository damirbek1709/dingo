<?php

/** @var yii\web\View $this */

use app\models\Page;
use yii\helpers\Html;

$model = Page::findOne($id);
$this->title = $model->title;
?>
<div class="site-about">
    <h1 class="general_title"><?= Html::encode($this->title) ?></h1>
    <?php
    echo $model->text;
    ?>
</div>
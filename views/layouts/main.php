<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>
    <header id="header">
        <?php
        NavBar::begin([
            'brandLabel' => Html::a(Html::img(Url::base() . "/images/site/logo.svg"), ['/']),
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-default navbar-inverse navbar-fixed-top navbar-owner',
            ],
            'innerContainerOptions' => ['class' => 'container-fluid'],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav'],
            'items' => [
                //['label' => 'Главная', 'url' => ['/site/index']],
                // [
                //     'label' => !Yii::$app->user->isGuest ? Yii::$app->user->identity->username : '',
                //     'items' => [
                //         ['label' => 'Панель модератора', 'url' => ['/moderator'], 'visible' => Yii::$app->user->can(permissionName: 'moderator')],
                //         ['label' => 'Панель хоста', 'url' => ['/owner'], 'visible' => Yii::$app->user->can('owner')],
                //         ['label' => 'Панель админа', 'url' => ['/admin'], 'visible' => Yii::$app->user->can('admin')],
                //         [
                //             'label' => 'Выход',
                //             'url' => ['/user/logout'],
                //             'linkOptions' => ['data-method' => 'post']
                //         ],
                //     ],
                //     'visible' => !Yii::$app->user->isGuest
                // ],
                // ['label' => 'Войти', 'url' => ['/user/signin']],
            ]
        ]);
        NavBar::end();
        ?>
    </header>

    <main id="main" class="flex-shrink-0" role="main">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer id="footer" class="mt-auto py-3 bg-light">
        <div class="container">
            <div class="row text-muted">
                <div class="col-md-6 text-md-start">&copy; dingo.kg <?= date('Y') ?></div>
                <div class="col-md-6 text-md-end"><?= Yii::powered() ?></div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
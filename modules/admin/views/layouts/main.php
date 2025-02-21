<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\modules\admin\assets\ModuleAsset;
use yii\helpers\Url;

ModuleAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerLinkTag(['rel' => 'apple-touch-icon', 'sizes' => '180x180', 'href' => Url::to(['/apple-touch-icon.png'])]); ?>
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'sizes' => '32x32', 'href' => Url::to(['/favicon-32x32.png'])]); ?>
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'sizes' => '16x16', 'href' => Url::to(['/favicon-16x16.png'])]); ?>
    <?php $this->registerLinkTag(['rel' => 'manifest', 'href' => Url::to(['/site.webmanifest'])]); ?>
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->getModule('admin')->name,
            'brandUrl' => ['/admin/default/index'],
            'options' => [
                'class' => 'navbar-default navbar-inverse navbar-fixed-top',
            ],
            'innerContainerOptions' => ['class' => 'container-fluid'],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                ['label' => 'Категории', 'items' => [
                    ['label' => 'Минимизировано', 'url' => ['/admin/category/minimized'], 'visible' => Yii::$app->user->can('admin')],
                    //['label' => 'Полностью', 'url' => ['/admin/category'], 'visible' => Yii::$app->user->can('admin')],
                ], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Справочники', 'url' => ['/admin/directory'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Верификация пользователей', 'url' => ['/user/admin/verify-list'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Бизнес аккаунты', 'url' => ['/user/admin/business-account-list'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Виды жалоб', 'url' => ['/admin/complaint-type'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Отзывы разраб-кам', 'url' => ['/admin/feedback'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Платные услуги', 'url' => ['/admin/paid-service'], 'visible' => Yii::$app->user->can('admin')],
                ['label' => 'Вход', 'url' => ['/user/registration/signin/'], 'visible' => Yii::$app->user->isGuest],
                ['label' => !Yii::$app->user->isGuest ? Yii::$app->user->identity->username : '', 'items' => [
                    ['label' => 'Панель модератора', 'url' => ['/moderator'], 'visible' => Yii::$app->user->can('moderator')],
                    ['label' => 'Панель оператора', 'url' => ['/operator'], 'visible' => Yii::$app->user->can('operator')],
                    ['label' => 'Аккаунт', 'url' => ['/user/settings/account'], 'visible' => !Yii::$app->user->isGuest],
                    [
                        'label' => 'Выход',
                        'url' => ['/user/logout'],
                        'linkOptions' => ['data-method' => 'post']
                    ],
                ], 'visible' => !Yii::$app->user->isGuest],
            ],
        ]);
        NavBar::end();
        ?>

        <div class="container-fluid">
            <div style="padding-top: 70px;">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) && Yii::$app->controller->route !== 'user/security/login' ? $this->params['breadcrumbs'] : [],
                ]) ?>
            </div>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <p class="pull-left">&copy; Selva <?= date('Y') ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\models\Objects;
use app\modules\owner\assets\ModuleAsset;

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php

        $object_arr = Objects::objectList();
        $user_string = !Yii::$app->user->isGuest ? substr(Yii::$app->user->identity->username, 0, 1) : '';

        NavBar::begin([
            'brandLabel' => Html::a(Html::img(Url::base() . "/images/site/logo.svg"), ['/']),
            'brandUrl' => ['/'],
            'options' => [
                'class' => 'navbar-default navbar-inverse navbar-fixed-top navbar-owner',
            ],
            'innerContainerOptions' => ['class' => 'container-fluid'],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                $object_arr,
                [
                    'label' => '',
                    'url' => '#',
                    'options' => ['class' => 'menu-notification-icon']
                ],
                [
                    'label' => $user_string,
                    'options' => [
                        'class' => 'user-link-class'
                    ],
                    'items' => [
                        // ['label' => 'Панель модератора', 'url' => ['/moderator'], 'visible' => Yii::$app->user->can('moderator')],
                        // ['label' => 'Панель управления', 'url' => ['/admin'], 'visible' => Yii::$app->user->can('admin')],
                        [
                            'label' => 'Аккаунт',
                            'url' => ['/user/view-account'],
                            'visible' => !Yii::$app->user->isGuest,

                        ],
                        [
                            'label' => 'Выход',
                            'url' => ['/user/logout'],
                            'linkOptions' => ['data-method' => 'post']
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest
                ],
            ],
        ]);
        NavBar::end();
        ?>

        <div class="container-fluid gray-content">
            <div style="padding-top: 70px;">
                <?php /*echo Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) && Yii::$app->controller->route !== 'user/security/login' ? $this->params['breadcrumbs'] : [],
                ]) */?>
            </div>
            <?php //echo Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <p class="pull-left">&copy; dingo.kg <?= date('Y') ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
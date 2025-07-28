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
        <header class="header">
            <div class="logo">Dingo</div>
            <?php
            $object_arr = Objects::objectListMenu();
            ?>
            <nav class="nav-right">
                <div class="desktop-nav">
                    

                    <?php echo Html::dropDownList('object_id', $object_arr, ['class' => 'dropdown-select']); ?>

                    <button class="icon-btn">
                        <svg class="bell-icon" viewBox="0 0 24 24">
                            <path
                                d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" />
                        </svg>
                    </button>

                    <button class="profile-btn">A</button>
                </div>

                <div class="mobile-nav">
                    <select class="dropdown-select">
                        <option value="karakol-zhai">Апартаменты Каракол-Жай</option>
                        <option value="apartment-1">Апартаменты Центр</option>
                        <option value="apartment-2">Апартаменты Север</option>
                        <option value="apartment-3">Апартаменты Восток</option>
                        <option value="apartment-4">Апартаменты Запад</option>
                    </select>

                    <button class="hamburger">
                        <div class="hamburger-icon">
                            <div class="hamburger-line"></div>
                            <div class="hamburger-line"></div>
                            <div class="hamburger-line"></div>
                        </div>
                    </button>
                </div>
            </nav>
        </header>
        <?php

        // $object_arr = Objects::objectList();
        // $user_string = !Yii::$app->user->isGuest ? substr(Yii::$app->user->identity->username, 0, 1) : '';

        // NavBar::begin([
        //     'brandLabel' => Html::a(Html::img(Url::base() . "/images/site/logo.svg"), ['/']),
        //     'brandUrl' => ['/owner/default/index'],
        //     'options' => [
        //         'class' => 'navbar-default navbar-inverse navbar-fixed-top navbar-owner',
        //     ],
        //     'innerContainerOptions' => ['class' => 'container-fluid'],
        // ]);
        // echo Nav::widget([
        //     'options' => ['class' => 'navbar-nav navbar-right'],
        //     'items' => [
        //         $object_arr,
        //         // [
        //         //     'label' => '',
        //         //     'url' => '#',
        //         //     'options' => ['class' => 'menu-notification-icon']
        //         // ],
        //         [
        //             'label' => $user_string,
        //             'options' => [
        //                 'class' => 'user-link-class'
        //             ],
        //             'items' => [
        //                 // ['label' => 'Панель модератора', 'url' => ['/moderator'], 'visible' => Yii::$app->user->can('moderator')],
        //                 // ['label' => 'Панель управления', 'url' => ['/admin'], 'visible' => Yii::$app->user->can('admin')],
        //                 [
        //                     'label' => 'Аккаунт',
        //                     'url' => ['/user/view-account'],
        //                     'visible' => !Yii::$app->user->isGuest,

        //                 ],
        //                 [
        //                     'label' => 'Выход',
        //                     'url' => ['/user/logout'],
        //                     'linkOptions' => ['data-method' => 'post']
        //                 ],
        //             ],
        //             'visible' => !Yii::$app->user->isGuest
        //         ],
        //     ],
        // ]);
        // NavBar::end();
        ?>

        <div class="container-fluid gray-content">
            <div style="padding-top: 70px;">
                <?php /*echo Breadcrumbs::widget([
'links' => isset($this->params['breadcrumbs']) && Yii::$app->controller->route !== 'user/security/login' ? $this->params['breadcrumbs'] : [],
]) */ ?>
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
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #f5f5f5;
    }

    .header {
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .header {
            padding: 12px 16px;
        }
    }

    .logo {
        background-color: white;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 24px;
        font-weight: bold;
        color: #4a90e2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .desktop-nav {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mobile-nav {
        display: none;
    }

    .hamburger {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
    }

    .hamburger-icon {
        width: 24px;
        height: 18px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .hamburger-line {
        width: 100%;
        height: 2px;
        background-color: white;
        border-radius: 1px;
        transition: all 0.2s ease;
    }

    @media (max-width: 768px) {
        .desktop-nav {
            display: none;
        }

        .mobile-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }
    }

    .dropdown-select {
        background-color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 20px;
        font-size: 14px;
        color: #333;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="8" viewBox="0 0 12 8"><path fill="%23666" d="M6 8L0 0h12z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px 8px;
        padding-right: 40px;
        min-width: 200px;
    }

    .dropdown-select:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
    }

    .dropdown-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.3);
    }

    @media (max-width: 768px) {
        .dropdown-select {
            min-width: 180px;
            font-size: 13px;
            padding: 8px 12px;
            padding-right: 32px;
        }
    }

    .icon-btn {
        background-color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .icon-btn:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
    }

    .bell-icon {
        width: 18px;
        height: 18px;
        fill: #666;
    }

    .profile-btn {
        background-color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #4a90e2;
        font-size: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .profile-btn:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
    }
</style>
<?php $this->endPage() ?>
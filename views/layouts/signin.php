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
use app\models\Objects;

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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>
    <div class="wrap">
        <header class="header_menu">
            <div class="logo"><?= Html::a(Html::img(Url::base() . "/images/site/logo.svg"), ['/']); ?></div>
        </header>

        <main id="main" class="flex-shrink-0" role="main">
            <div class="container">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>
    </div>

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


<style>
    

    .header_menu {
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    @media (max-width: 768px) {
        .header_menu {
            padding: 12px 16px;
        }
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
        position: relative;
        z-index: 1001;
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
        transition: all 0.3s ease;
    }

    .hamburger.active .hamburger-line:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .hamburger.active .hamburger-line:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active .hamburger-line:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -6px);
    }

    /* Mobile Dropdown Styles */
    .mobile-dropdown {
        position: absolute;
        top: 100%;
        right: 20px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        min-width: 220px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        overflow: hidden;
    }

    .mobile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .mobile-menu-item {
        border-bottom: 1px solid #f0f0f0;
    }

    .mobile-menu-item:last-child {
        border-bottom: none;
    }

    .mobile-icon-btn,
    .mobile-profile-btn {
        width: 100%;
        background: none;
        border: none;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-size: 14px;
        color: #333;
        transition: background-color 0.2s ease;
    }

    .mobile-profile-initial {
        width: 32px;
        height: 32px;
        background-color: #4a90e2;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
    }

    .mobile-menu-link {
        display: block;
        padding: 16px 20px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.2s ease;
    }


    .mobile-menu-link.logout-link {
        color: #dc3545;
    }

    .mobile-menu-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 8px 0;
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

        .mobile-dropdown {
            right: 16px;
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

    /* Profile Container & Dropdown */
    .profile-container {
        position: relative;
    }

    .profile-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        min-width: 280px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        overflow: hidden;
    }

    .profile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .profile-dropdown-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-avatar {
        width: 48px;
        height: 48px;
        background-color: #4a90e2;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
        text-transform: uppercase;
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 2px;
    }

    .profile-email {
        font-size: 13px;
        color: #666;
    }

    .profile-dropdown-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 0;
    }

    .profile-menu-item {
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-menu-item:last-child {
        border-bottom: none;
    }

    .profile-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.2s ease;
    }


    .profile-menu-link.logout-link {
        color: #dc3545;
    }

    .profile-menu-icon {
        width: 18px;
        height: 18px;
        fill: currentColor;
        flex-shrink: 0;
    }
</style>
<?php $this->endPage() ?>
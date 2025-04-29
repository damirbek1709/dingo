<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
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
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!--metatextblock-->
    <title>Mobile app</title>
    <meta name="description" content="A web application landing page">
    <meta property="og:url" content="http://dingo-kg.tilda.ws/mainhtml">
    <meta property="og:title" content="Mobile app">
    <meta property="og:description" content="A web application landing page">
    <meta property="og:type" content="website">
    <meta property="og:image"
        content="https://static.tildacdn.info/tild3332-3832-4137-b431-636665613238/-/resize/504x/LOGO.png">
    <link rel="canonical" href="http://dingo-kg.tilda.ws/mainhtml"> <!--/metatextblock-->
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <link rel="dns-prefetch" href="https://ws.tildacdn.com/">
    <link rel="dns-prefetch" href="https://static.tildacdn.info/">
    <meta name="robots" content="nofollow">
    <link rel="shortcut icon" href="https://static.tildacdn.info/img/tildafavicon.ico" type="image/x-icon">
    <!-- Assets -->
    <script type="text/javascript" async="" id="tildastatscript"
        src="./Mobile app2_files/tilda-stat-1.0.min.js"></script>
    <script type="text/javascript" async="" id="tildastatscript"
        src="./Mobile app2_files/tilda-stat-1.0(1).min.js"></script>
    <script src="./Mobile app2_files/tilda-fallback-1.0.min.js" async="" charset="utf-8"></script>
    <link rel="stylesheet" href="./Mobile app2_files/tilda-grid-3.0.min.css" type="text/css" media="all"
        onerror="this.loaderr=&#39;y&#39;;">
    <link rel="stylesheet" href="./Mobile app2_files/tilda-blocks-page67959483.min.css" type="text/css" media="all"
        onerror="this.loaderr=&#39;y&#39;;">
    <link rel="stylesheet" href="./Mobile app2_files/tilda-slds-1.4.min.css" type="text/css" media="all"
        onload="this.media=&#39;all&#39;;" onerror="this.loaderr=&#39;y&#39;;"> <noscript>
        <link rel="stylesheet" href="https://static.tildacdn.info/css/tilda-slds-1.4.min.css" type="text/css"
            media="all" />
    </noscript>
    <link rel="stylesheet" href="./Mobile app2_files/tilda-zoom-2.0.min.css" type="text/css" media="all"
        onload="this.media=&#39;all&#39;;" onerror="this.loaderr=&#39;y&#39;;"> <noscript>
        <link rel="stylesheet" href="https://static.tildacdn.info/css/tilda-zoom-2.0.min.css" type="text/css"
            media="all" />
    </noscript>
    <link rel="stylesheet" href="./Mobile app2_files/fonts-tildasans.css" type="text/css" media="all"
        onerror="this.loaderr=&#39;y&#39;;">
    <script nomodule="" src="./Mobile app2_files/tilda-polyfill-1.0.min.js" charset="utf-8"></script>
    <script
        type="text/javascript">function t_onReady(func) { if (document.readyState != 'loading') { func(); } else { document.addEventListener('DOMContentLoaded', func); } }
            function t_onFuncLoad(funcName, okFunc, time) { if (typeof window[funcName] === 'function') { okFunc(); } else { setTimeout(function () { t_onFuncLoad(funcName, okFunc, time); }, (time || 100)); } }</script>
    <script src="./Mobile app2_files/tilda-scripts-3.0.min.js" charset="utf-8" defer=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-blocks-page67959483.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-lazyload-1.0.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-menu-1.0.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-slds-1.4.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/hammer.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-zoom-2.0.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-skiplink-1.0.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script src="./Mobile app2_files/tilda-events-1.0.min.js" charset="utf-8" async=""
        onerror="this.loaderr=&#39;y&#39;;"></script>
    <script type="text/javascript">window.dataLayer = window.dataLayer || [];</script>
    <script type="text/javascript">(function () {
            if ((/bot|google|yandex|baidu|bing|msn|duckduckbot|teoma|slurp|crawler|spider|robot|crawling|facebook/i.test(navigator.userAgent)) === false && typeof (sessionStorage) != 'undefined' && sessionStorage.getItem('visited') !== 'y' && document.visibilityState) {
                var style = document.createElement('style'); style.type = 'text/css'; style.innerHTML = '@media screen and (min-width: 980px) {.t-records {opacity: 0;}.t-records_animated {-webkit-transition: opacity ease-in-out .2s;-moz-transition: opacity ease-in-out .2s;-o-transition: opacity ease-in-out .2s;transition: opacity ease-in-out .2s;}.t-records.t-records_visible {opacity: 1;}}'; document.getElementsByTagName('head')[0].appendChild(style); function t_setvisRecs() { var alr = document.querySelectorAll('.t-records'); Array.prototype.forEach.call(alr, function (el) { el.classList.add("t-records_animated"); }); setTimeout(function () { Array.prototype.forEach.call(alr, function (el) { el.classList.add("t-records_visible"); }); sessionStorage.setItem("visited", "y"); }, 400); }
                document.addEventListener('DOMContentLoaded', t_setvisRecs);
            }
        })();</script>
    <style type="text/css">
        @media screen and (min-width: 980px) {
            .t-records {
                opacity: 0;
            }

            .t-records_animated {
                -webkit-transition: opacity ease-in-out .2s;
                -moz-transition: opacity ease-in-out .2s;
                -o-transition: opacity ease-in-out .2s;
                transition: opacity ease-in-out .2s;
            }

            .t-records.t-records_visible {
                opacity: 1;
            }
        }
    </style>
    <style type="text/css">
        @media screen and (min-width: 980px) {
            .t-records {
                opacity: 0;
            }

            .t-records_animated {
                -webkit-transition: opacity ease-in-out .2s;
                -moz-transition: opacity ease-in-out .2s;
                -o-transition: opacity ease-in-out .2s;
                transition: opacity ease-in-out .2s;
            }

            .t-records.t-records_visible {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="t-body" style="margin:0;">
    <?php $this->beginBody() ?>
    <main id="main" class="flex-shrink-0" role="main">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
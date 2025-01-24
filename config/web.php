<?php

use Symfony\Component\Mailer\Mailer;
$params = require __DIR__ . '/params.php';
$db = file_exists(__DIR__ . '/db-local.php') ?
    require(__DIR__ . '/db-local.php') : require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic',
    'language' => 'ru',
    'name' => 'Dingo.kg',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'languageSwitcher'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'confirmWithin' => 21600,
            'cost' => 12,
            'enableRegistration' => true,
            'enableConfirmation' => true,
            'enableGeneratingPassword' => true,
            'enablePasswordRecovery' => false,
            'enableFlashMessages' => false,
            'admins' => ['admin'],
            'modelMap' => [
                'User' => 'app\models\user\User',
                'Token' => 'app\models\user\Token',
                'RegistrationForm' => 'app\models\user\RegistrationForm',
            ],
            'controllerMap' => [
                ''
            ],
        ],

        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            'imagesStorePath' => 'uploads/images/store',
            'imagesCachePath' => 'uploads/images/cache',
            'graphicsLibrary' => 'Imagick',
            'placeHolderPath' => '@webroot/images/site/template.png',
            //'imageCompressionQuality' => 100,
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@app/views/user'
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'efRqaUcSjyACc0wrv4ogfjbZGizExLfh',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'languageSwitcher' => [
            'class' => 'app\widgets\languageSwitcher',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // 'mailer' => [
        //     'class' => 'Swift_SmtpTransport',
        //     'host' => 'smtp.yandex.ru',
        //     'username' => 'no.reply@biovit.kg',
        //     'password' => 'Biovit@kg$!',
        //     'port' => '465',
        //     'encryption' => 'ssl',
        // ],

        'mailer' => [
            //'class' => \yii\symfonymailer\Mailer::class,
            'class' => Mailer::class,
            'transport' => [
                'dsn' => 'smtp://no.reply@biovit.kg:Biovit@kg$!.com:465',
            ],
            'useFileTransport' => false, // Set to true to save emails as files during development
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'contact' => 'site/contact',
                'about' => 'site/about',
                'shipment' => 'site/shipment',
                'return' => 'site/return',
                'privacy' => 'site/privacy',
                'self-pickup' => 'site/self-pickup',
                'payment' => 'site/payment',
                'atelier' => 'site/atelier',
                'clients' => 'site/clients',
                'offer' => 'site/offer',
                'projects' => 'site/projects',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // $config['bootstrap'][] = 'debug';
    // $config['modules']['debug'] = [
    //     'class' => 'yii\debug\Module',
    //     // uncomment the following to add your IP if you are not connecting from localhost.
    //     'allowedIPs' => ['*'],
    // ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;

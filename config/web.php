<?php

$params = require __DIR__ . '/params.php';
$db = file_exists(__DIR__ . '/db-local.php') ?
    require(__DIR__ . '/db-local.php') : require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic',
    'language' => 'ru',
    'name' => 'Dingo.kg',
    'basePath' => dirname(__DIR__),
    //'defaultRoute' => '/user/signin',
    'bootstrap' => ['log', 'languageSwitcher'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
            'name' => 'Панель управления',
        ],

        'owner' => [
            'class' => 'app\modules\owner\Module',
            'name' => 'Панель управления',
        ],
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
                'registration' => 'app\controllers\user\RegistrationController',
                'security' => 'app\controllers\user\SecurityController',
                // 'admin' => 'app\controllers\user\AdminController',
                // 'profile' => 'app\controllers\user\ProfileController',
                'settings' => 'app\controllers\user\SettingsController',
            ],
            'urlRules' => [
                '<id:\d+>' => 'profile/show',
                '<action:(login|logout|auth)>' => 'security/<action>',
                '<action:(register|resend|signin|confirm-number)>' => 'registration/<action>',
                'confirm/<id:\d+>/<code:[A-Za-z0-9_-]+>' => 'registration/confirm',
                'forgot' => 'recovery/request',
                'confirm-code' => 'registration/confirm-code',
                'recover/<id:\d+>/<code:[A-Za-z0-9_-]+>' => 'recovery/reset',
                'settings/<action:\w+>' => 'settings/<action>',
            ],
        ],
        'rbac' => 'dektrium\rbac\RbacWebModule',
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
                    '@dektrium/user/views' => '@app/views/user',
                    '@dektrium/rbac/views' => '@app/views/user/rbac'
                ],
            ],
        ],

        'flashPay' => [
            'class' => 'app\components\FlashPay',
            'merchantId' => 'YOUR_MERCHANT_ID',
            'secretKey' => 'YOUR_SECRET_KEY',
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

        'resendMailer' => [
            'class' => 'app\components\ResendMailer',
            'apiKey' => 're_atcXWkEq_LaWuaA5QKX5GmHNpWyFbGKDx',
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.resend.com',
                'username' => 'resend',
                'password' => 're_MrDjBCEM_13XEtNrkHUQrSoWusNBh72T4',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],

        'meili' => [
            'class' => 'app\components\Meili',
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
                'admin' => 'admin/object',
                'projects' => 'site/projects',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                'owner/object/booking' => 'owner/booking',
                'user/edit-account' => 'user/settings/edit-profile',
                'user/view-account' => 'user/settings/profile',
                'user/delete-account' => 'user/settings/delete-account',
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


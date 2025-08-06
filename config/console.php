<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$nikita = file_exists(__DIR__ . '/nikita-local.php') ?
    require(__DIR__ . '/nikita-local.php') : require(__DIR__ . '/nikita.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'nikita' => $nikita,
        'meili' => [
            'class' => 'app\components\Meili',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
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
    ],
    'modules' => [
        'rbac' => 'dektrium\rbac\RbacConsoleModule',
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

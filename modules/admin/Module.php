<?php

namespace app\modules\admin;

use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    public $name = 'Admin Panel';
    public $layout = "main";

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';
    public $defaultRoute = 'admin';
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Yii::$app->errorHandler->errorAction = '/admin/default/error';
    }
}

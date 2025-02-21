<?php

namespace app\modules\owner;

use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    public $name = 'Admin Panel';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\owner\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Yii::$app->errorHandler->errorAction = '/owner/default/error';
    }
}

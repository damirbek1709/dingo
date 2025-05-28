<?php

namespace app\controllers\user;

use dektrium\user\controllers\SecurityController as BaseSecurityController;
use Yii;
use dektrium\user\Finder;
use dektrium\user\models\Account;
use dektrium\user\models\LoginForm;
use dektrium\user\models\User;
class SecurityController extends BaseSecurityController
{
    public $layout;
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }

        /** @var LoginForm $model */
        $model = \Yii::createObject(LoginForm::className());
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $this->trigger(self::EVENT_AFTER_LOGIN, $event);
            if (Yii::$app->user->can('admin')) {
                return $this->redirect('/admin/object');
            }
            return $this->goHome();
        }

        return $this->render('login', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }
}

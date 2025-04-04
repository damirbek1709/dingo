<?php

namespace app\controllers\user;

use app\models\user\User;
use dektrium\user\controllers\SettingsController as BaseSettingsController;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use dektrium\user\models\Profile;

class SettingsController extends BaseSettingsController
{
    public $layout;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                    //'delete'     => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['profile', 'account', 'networks', 'disconnect', 'delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['confirm'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }
    /**
     * Shows user's profile.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */

    /** @inheritdoc */

    /**
     * Shows profile settings form.
     *
     * @return string|\yii\web\Response
     */
    public function actionProfile()
    {
        $model = $this->finder->findProfileById(\Yii::$app->user->identity->getId());
        $user = User::findOne(Yii::$app->user->id);

        if ($model == null) {
            $model = \Yii::createObject(Profile::className());
            $model->link('user', \Yii::$app->user->identity);
        }

        $event = $this->getProfileEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->save() && $user->load(\Yii::$app->request->post()) && $user->save(false)) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Your profile has been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model,
            'user' => $user
        ]);
    }

    public function actionDelete()
    {
        /** @var User $user */
        $user = User::findOne(Yii::$app->user->id);
        $user->username = preg_replace('/^996/', rand(100, 999), $user->username);
        $user->full_name = "Deleted User";
        $user->confirmed_at = null;

        $time = time();
        $length = strlen($user->auth_key) - strlen($time);

        $user->auth_key = substr($user->auth_key, 0, $length) . $time;

        $user->flags = User::FLAG_USER_DELETED;
        $dao = Yii::$app->db;

        if ($user->save()) {
            $dao->createCommand()->delete('fcm_token', ['user_id' => $user->id, 'app_id' => 1])->execute();
            \Yii::$app->session->setFlash('info', \Yii::t('user', 'Your account has been completely deleted'));
            \Yii::$app->user->logout();
            return $this->goHome();
        }
    }

}

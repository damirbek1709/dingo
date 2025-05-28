<?php

namespace app\controllers\user;

use app\models\user\User;
use dektrium\user\controllers\SettingsController as BaseSettingsController;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use dektrium\user\models\Profile;
use app\models\Booking;

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
                        'actions' => ['profile', 'account', 'networks', 'disconnect', 'delete', 'edit-profile', 'delete-account'],
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
    // public function actionProfile()
    // {
    //     $model = $this->finder->findProfileById(\Yii::$app->user->identity->getId());
    //     $user = User::findOne(Yii::$app->user->id);

    //     if ($model == null) {
    //         $model = \Yii::createObject(Profile::className());
    //         $model->link('user', \Yii::$app->user->identity);
    //     }

    //     $event = $this->getProfileEvent($model);

    //     $this->performAjaxValidation($model);

    //     $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
    //     if ($model->load(\Yii::$app->request->post()) && $model->save() && $user->load(\Yii::$app->request->post()) && $user->save(false)) {
    //         \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Your profile has been updated'));
    //         $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
    //         return $this->refresh();
    //     }

    //     return $this->render('profile', [
    //         'model' => $model,
    //         'user' => $user
    //     ]);
    // }


    public function actionProfile()
    {
        $this->layout = "/general";
        $user_id = Yii::$app->user->id;
        $model = User::findOne($user_id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionEditProfile()
    {
        $this->layout = "/general";
        $id = Yii::$app->user->id;
        $model = User::findOne($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['/user/view-account']);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDeleteAccount()
    {
        $this->layout = "/general";
        $id = Yii::$app->user->id;
        $user = User::findOne($id);

        $user->confirmed_at = null;
        $rand = $id.rand(1000,9999);
        $user->username = "deleted_user_".$rand;
        $user->email = "deleted_user_".$rand;
        $user->name = "Deleted User";
        $user->flags = User::FLAG_DELETED;

        $booking = Booking::find()->where(['user_id' => Yii::$app->user->id, 'status' => Booking::PAID_STATUS_PAID])->andWhere(['>', 'date_from', date('Y-m-d')])->one();
        if ($user->save(false)) {
            Yii::$app->getUser()->logout();
            $message = Yii::t("app", "Удаление учётной записи прошло успешно");
            if ($booking) {
                $message = Yii::t("app", "Удаление учётной записи прошло успешно, но на вашем объекте действующая бронь, ");
            }
            Yii::$app->session->setFlash('success', $message);
            return $this->redirect(['/user/signin']);
        }
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

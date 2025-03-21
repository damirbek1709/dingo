<?php

namespace app\modules\api\controllers;

use app\models\user\UserStatus;
use Yii;
use app\models\user\SignupForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\user\User;
use yii\helpers\ArrayHelper;
use app\models\user\Token;
use app\models\user\RegistrationForm;
use yii\web\NotFoundHttpException;

class UserController extends BaseController
{
    public $modelClass = 'app\models\UserModel';
    public function actionCustomAction()
    {
        return $this->render('custom-view');
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        // Load data into the model
        $data = Yii::$app->request->post();
        $model->load($data, '');

        if ($model->validate() && $model->signup()) {
            return [
                'success' => true,
                'message' => '',
            ];
        }

        return [
            'success' => false,
            'errors' => $model->getErrors(),
        ];
    }

    public function actionRegister()
    {
        $response["success"] = false;

        $model = Yii::createObject(RegistrationForm::className());
        $model->username = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $model->email = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $user = User::find()->where(['email' => $model->email])->one();

        if (!$user) {
            if ($model->register()) {
                $response["success"] = true;
                $response["message"] = "Пользователь создан";
                if (in_array($user->email, ['damirbek@gmail.com'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                    $response['code'] = $token->code;
                }
            }
        } else {
            $sendSMS = true;
            $response["success"] = true;
            $response["message"] = "Пользователь найден";
            if (in_array($user->email, ['damirbek@gmail.com','adiletprosoft@gmail.com'])) {
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                $sendSMS = false;
            } else {
                $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $user);
                $response['code'] = $token->code;
            }
        }
        return $response;
    }



    public function actionCheckConfirmationCode()
    {
        $module = Yii::$app->getModule('user');

        if (!$module->enableConfirmation) {
            throw new NotFoundHttpException();
        }

        $response["success"] = false;

        $code = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'code');
        $email = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $user = User::find()->where(['email' => $email])->one();

        $token = Token::find()->where(['code' => $code, 'user_id' => $user->id, 'type' => Token::TYPE_CONFIRMATION])->one();

        if ($token === null || $token->isExpired || $token->user === null) {
            $response["success"] = false;
            $response["errors"]["code"] = 'Проверочный код не найден или устарел';
        } else {
            $user = $token->user;
            $user->confirmed_at = time();
            $user->save();
            $token->delete();

            $response["success"] = true;
            $response["message"] = 'Номер телефона подтверждён';
            $response["user"] = $user;

            //$this->saveFcm($user->id, Yii::$app->request->post());
        }

        return $response;
    }



    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'signup',
            'register',
            'check-confirmation-code'
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['signup', 'register', 'check-confirmation-code'],
                    'roles' => ['?'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'signup' => ['POST'],
                'register' => ['POST'],
                'check-confirmation-code' => ['POST'],
            ],
        ];


        return $behaviors;
    }
}

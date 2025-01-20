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
        $model->password = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'password');

        $username = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $user = User::find()->where(['email' => $username])->one();

        if (!$user) {
            if ($model->register()) {
                $response["success"] = true;
                $response["message"] = "Пользователь создан";
            } else {
                $sendSMS = true;
                if (in_array($user->username, ['damirbek2@gmail.com'])) {
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


        }
        return $response;
    }

    // public function actionRegister()
    // {
    //     if (!Yii::$app->getModule('user')->enableRegistration) {
    //         throw new NotFoundHttpException();
    //     }

    //     $response["success"] = false;
    //     $username = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
    //     $user = User::find()->where(['email' => $username])->one();
    //     if (!$user) {
    //         $model = new RegistrationForm();
    //         $model->username = $username;
    //         $model->email = $username;

    //         if ($model->validate() && $model->register()) {
    //             $response["success"] = true;
    //             $response["message"] = 'Пользователь создан';
    //         } else {
    //             $response["errors"] = $model->errors;
    //         }
    //     } else {
    //         $user->confirmed_at = null;
    //         $user->save();
    //         $sendSMS = true;

    //         if (in_array($user->username, ['996553000665', '996707889512', '996551170990', '996505170990', '996555555555', '996333333333', '996777777777', '996999999999'])) {
    //             $dao = Yii::$app->db;
    //             $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
    //             $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
    //             $sendSMS = false;
    //         } else {
    //             $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
    //             $token->link('user', $user);
    //         }

    //         $recipient = '+' . $user->username;

    //         if ($sendSMS) {
    //             // Yii::$app->nikita->setRecipient($recipient)
    //             //     ->setText('Ваш код: ' . $token->code . ' is your code' . PHP_EOL . 'wYvKRPwmEXI')
    //             //     ->send();
    //         }
    //         $response["success"] = true;
    //         $response["message"] = 'Пользователь найден';
    //     }

    //     return $response;
    // }

    public function actionCheckConfirmationCode()
    {
        $module = Yii::$app->getModule('user');

        if (!$module->enableConfirmation) {
            throw new NotFoundHttpException();
        }

        $response["success"] = false;

        $code = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'code');

        $token = Token::find()->where(['code' => $code, 'type' => Token::TYPE_CONFIRMATION])->one();

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

            $this->saveFcm($user->id, Yii::$app->request->post());
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

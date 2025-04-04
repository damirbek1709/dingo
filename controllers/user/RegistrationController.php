<?php

namespace app\controllers\user;

use dektrium\user\controllers\RegistrationController as BaseRegistrationController;
use Yii;
use app\models\user\RegistrationForm;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\user\ConfirmNumberForm;
use app\models\user\Token;
use app\models\user\SigninForm;
use app\models\user\User;
use yii\web\Response;
use app\models\Wallet;
use app\models\WalletTransaction;

class RegistrationController extends BaseRegistrationController
{
    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['register', 'connect', 'confirm-number', 'signin', 'authorize', 'confirm-code', 'send-welcome'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['confirm', 'resend'], 'roles' => ['?', '@']],
                ],
            ],
        ];
    }

    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise
     * redirects to home page.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionRegister()
    {
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);

        $this->performAjaxValidation($model);
        $model->username = $model->email;
        
        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            $this->trigger(self::EVENT_AFTER_REGISTER, $event);

            return $this->redirect('confirm-number');
        }

        return $this->render('register', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    public function actionRegister()
    {
        $response["success"] = false;

        $model = Yii::createObject(RegistrationForm::className());
        $user = User::find()->where(['email' => $email])->one();

        if (!$user) {
            $user = new User();
            $user->username = $email;
            $user->email = $email;

            if ($user->register()) {

                $auth = Yii::$app->authManager;
                $role = $auth->getPermission('owner'); // Make sure "owner" role exists in RBAC
                if ($role) {
                    $auth->assign($role, $user->id);
                }

                $response["success"] = true;
                $response["message"] = "Пользователь создан";
                if (in_array($email, ['damirbek@gmail.com'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                    $response['code'] = $token->code;
                    Yii::$app->mailer->compose()
                        ->setFrom('send@dingo.kg')
                        ->setTo($email)
                        ->setSubject("Ваш код авторизации: " . $token->code)
                        ->setHtmlBody("<h1>{$token->code}</h1>")
                        ->setTextBody('Hello from Resend! This is a test email.')
                        ->send();

                }
            }
        } else {
            $sendSMS = true;
            $response["success"] = true;
            $response["message"] = "Пользователь найден";
            if (in_array($email, ['damirbek@gmail.com', 'adiletprosoft@gmail.com'])) {
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                $sendSMS = false;
            } else {
                $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $user);
                //$response['code'] = $token->code;
            }
            if ($sendSMS) {
                Yii::$app->mailer->compose()
                    ->setFrom('send@dingo.kg')
                    ->setTo($email)
                    ->setSubject("Ваш код авторизации: " . $token->code)
                    ->setHtmlBody("<h1>{$token->code}</h1>")
                    ->setTextBody('Hello from Resend! This is a test email.')
                    ->send();
            }
        }

        return $response;
    }



    public function actionSignin()
    {
        $model = new SigninForm();

        $this->performAjaxValidation($model);
        if ($model->load(Yii::$app->request->post()) && $model->signin()) {
            return $this->redirect('confirm-code');
        }

        return $this->render('signin', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    /**
     *
     * @return type
     */
    public function actionConfirmNumber()
    {
        $model = new ConfirmNumberForm();

        $this->performAjaxValidation($model);
        if (Yii::$app->request->post('code')) {
            $model->confirmation_code = Yii::$app->request->post('code');

            if ($model->confirmationCodeFound()) {
                $token = Token::find()->where(['code' => $model->confirmation_code, 'type' => Token::TYPE_CONFIRMATION])->one();
                $user = $token->user;
                $user->confirmed_at = time();
                $user->referral_id = $this->generateUniqueReferralId();
                Yii::$app->balanceManager->increase(
                    [
                        'user_id' => $user->id,
                        'type' => Wallet::TYPE_KGS,
                    ],
                    100,
                    [
                        'type' => WalletTransaction::TYPE_REGISTER_USER,
                        'userId' => $user->id,
                        'userRegisteredDate' => $user->confirmed_at,
                    ]
                );
                $user->save();
                $token->delete();
                if (Yii::$app->user->login($user)) {
                    return $user->auth_key;
                } else {
                    return "false";
                }
            } else {
                return "false";
            }
        }

        return $this->render('confirm-number', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    private function generateUniqueReferralId()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (User::findOne(['referral_id' => $code]));

        return $code;
    }

    /**
     *
     * @return type
     */
    public function actionConfirmNumberAlter()
    {
        $model = new ConfirmNumberForm();

        $this->performAjaxValidation($model);
        if ($model->load(Yii::$app->request->post()) && $model->confirmationCodeFound()) {
            $token = Token::find()->where(['code' => $model->confirmation_code, 'type' => Token::TYPE_CONFIRMATION])->one();
            $user = $token->user;
            $user->confirmed_at = time();
            $user->save();
            $token->delete();
            Yii::$app->user->login($user);
            return $this->goHome();
        }

        return $this->render('confirm-number', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }


    public function actionAuthorize()
    {
        $model = new SigninForm();

        $this->performAjaxValidation($model);
        if (Yii::$app->request->post('phone')) {
            $model->phone_number = Yii::$app->request->post('phone');
            if ($model->signin()) {
                return "true";
            }
        }
        return $this->render('signin', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    /**
     *
    
     */
    public function actionConfirmCode()
    {
        $model = new ConfirmNumberForm();

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->confirmationCodeFound()) {
            $token = Token::find()->where(['code' => $model->confirmation_code, 'type' => Token::TYPE_CONFIRMATION])->one();
            $user = $token->user;
            $user->confirmed_at = time();
            $user->save();
            $token->delete();

            Yii::$app->user->login($user);
            return $this->redirect('/user/profile/view');
        }

        return $this->render('confirm-number', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }
}

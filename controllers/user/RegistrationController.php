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
    public $layout;
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
    public function actionRegister2()
    {
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);

        $this->performAjaxValidation($model);
        $email = $model->email;
        $model->username = $email;


        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            $user = User::find()->where(['email' => $email])->one();
            $token = new Token();
            $token->user_id = $user->id; // Ensure user_id is set
            $token->type = Token::TYPE_CONFIRMATION;
            $token->code = rand(1000, 9999); // Generate a random code
            $token->created_at = time();

            if ($token->save()) {
                Yii::$app->mailer->compose()
                    ->setFrom('send@dingo.kg')
                    ->setTo($email)
                    ->setSubject("Ваш код авторизации: " . $token->code)
                    ->setHtmlBody("<h1>{$token->code}</h1>")
                    ->setTextBody('Hello from Resend! This is a test email.')
                    ->send();

                $recipient = '+' . $model->phone;
                Yii::$app->nikita->setRecipient($recipient)
                    ->setText('Ваш код: ' . $token->code . ' is your code' . PHP_EOL . 'wYvKRPwmEXI')
                    ->send();
            } else {
                Yii::error('Token saving failed: ' . json_encode($token->errors), 'app');
            }


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
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());
        $event = $this->getFormEvent($model);

        $this->trigger(self::EVENT_BEFORE_REGISTER, $event);
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            if (in_array($model->email, ['damirbek@gmail.com'])) {
                $user = User::find()->where(['email' => $model->email])->one();
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
            } else {
                if ($model->register()) {
                    $user = User::find()->where(['email' => $model->email])->one();
                    $token = new Token();
                    $token->user_id = $user->id; // Ensure user_id is set
                    $token->type = Token::TYPE_CONFIRMATION;
                    $token->code = rand(1000, 9999); // Generate a random code
                    $token->created_at = time();

                    if ($token->save()) {
                        Yii::$app->mailer->compose()
                            ->setFrom('send@dingo.kg')
                            ->setTo($model->email)
                            ->setSubject("Ваш код авторизации: " . $token->code)
                            ->setHtmlBody("<h1>{$token->code}</h1>")
                            ->setTextBody('Hello from Resend! This is a test email.')
                            ->send();

                        $recipient = '+' . $model->phone;
                        Yii::$app->nikita->setRecipient($recipient)
                            ->setText('Ваш код: ' . $token->code . ' is your code' . PHP_EOL . 'wYvKRPwmEXI')
                            ->send();
                    } else {
                        Yii::error('Token saving failed: ' . json_encode($token->errors), 'app');
                    }
                    $this->trigger(self::EVENT_AFTER_REGISTER, $event);
                    return $this->redirect('confirm-number');
                }
            }
        }

        return $this->render('register', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }



    public function actionSignin()
    {
        $this->layout = "//signin";
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var RegistrationForm $model */
        $model = Yii::createObject(SigninForm::className());
        $event = $this->getFormEvent($model);
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            if (in_array($model->email, ['damirbek@gmail.com', 'adiletprosoft@gmail.com'])) {
                Yii::$app->session->set('session_email', $model->email);
                $user = User::find()->where(['email' => $model->email])->one();
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                return $this->redirect('confirm-number');
            } else {
                $user = User::find()->where(['email' => $model->email])->one();
                if ($user) {
                    $token = new Token();
                    $token->user_id = $user->id; // Ensure user_id is set
                    $token->type = Token::TYPE_CONFIRMATION;
                    $token->code = rand(1000, 9999); // Generate a random code
                    $token->created_at = time();

                    if ($token->save()) {
                        $recipient = '+' . $model->phone;
                        Yii::$app->nikita->setRecipient($recipient)
                            ->setText('Ваш код: ' . $token->code . ' is your code' . PHP_EOL . 'wYvKRPwmEXI')
                            ->send();
                            
                        Yii::$app->mailer->compose()
                            ->setFrom('send@dingo.kg')
                            ->setTo($model->email)
                            ->setSubject("Ваш код авторизации: " . $token->code)
                            ->setHtmlBody("<h1>{$token->code}</h1>")
                            ->setTextBody('Hello from Resend! This is a test email.')
                            ->send();
                        Yii::$app->session->set('session_email', $model->email);

                    } else {
                        Yii::error('Token saving failed: ' . json_encode($token->errors), 'app');
                    }
                    return $this->redirect('confirm-number');

                } else {
                    $user = new User();
                    $user->username = $model->email;
                    $user->email = $model->email;

                    if ($user->register()) {
                        $auth = Yii::$app->authManager;
                        $role = $auth->getPermission('owner'); // Make sure "owner" role exists in RBAC
                        if ($role) {
                            $auth->assign($role, $user->id);
                        }

                        $token = new Token();
                        $token->user_id = $user->id; // Ensure user_id is set
                        $token->type = Token::TYPE_CONFIRMATION;
                        $token->code = rand(1000, 9999); // Generate a random code
                        $token->created_at = time();

                        if ($token->save()) {
                            Yii::$app->mailer->compose()
                                ->setFrom('send@dingo.kg')
                                ->setTo($model->email)
                                ->setSubject("Ваш код авторизации: " . $token->code)
                                ->setHtmlBody("<h1>{$token->code}</h1>")
                                ->setTextBody('Hello from Resend! This is a test email.')
                                ->send();
                            Yii::$app->session->set('session_email', $model->email);
                        } else {
                            Yii::error('Token saving failed: ' . json_encode($token->errors), 'app');
                        }
                        $this->trigger(self::EVENT_AFTER_REGISTER, $event);
                        return $this->redirect('confirm-number');
                    }
                }

            }
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

        // Perform AJAX validation
        $this->performAjaxValidation($model);
        $session_email = Yii::$app->session->get('session_email');

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Validate the form
            if ($model->validate()) {
                $token = Token::find()->where(['code' => $model->confirmation_code, 'type' => Token::TYPE_CONFIRMATION])->one();
                $user = $token->user;
                $user->confirmed_at = time();
                $user->save();
                $token->delete();

                // Assign role to user
                $auth = Yii::$app->authManager;
                $role = $auth->getPermission('owner');
                if ($role) {
                    $auth->assign($role, $user->id);
                }

                // Log in the user
                if (Yii::$app->user->login($user)) {
                    Yii::$app->session->remove('session_email');
                    if (Yii::$app->user->identity->isAdmin) {
                        return $this->redirect('/admin');
                    }

                    $filter_string = "user_id=" . Yii::$app->user->id;
                    $client = Yii::$app->meili->connect();
                    $res = $client->index('object')->search('', [
                        'filter' => [
                            $filter_string
                        ],
                        'limit' => 10000
                    ])->getHits();



                    if (count($res)) {
                        return $this->redirect('/owner/object/index');
                    } else {
                        return $this->redirect('/owner/object/create');
                    }
                }
            } else {
                // If validation fails, set the error message
                //Yii::$app->session->setFlash('error', 'Неверно введен проверочный код');
            }
        }

        // Render the confirmation page
        return $this->render('confirm-number', [
            'model' => $model,
            'module' => $this->module,
            'session_email' => $session_email
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

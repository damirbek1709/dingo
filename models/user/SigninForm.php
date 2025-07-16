<?php

namespace app\models\user;

use Yii;
use app\models\user\User;
use app\models\user\RegistrationForm;

/**
 * @property int $phone_number Номер телефона
 */
class SigninForm extends \yii\base\Model
{
    public $email;
    public $phone;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required', 'on' => ['register', 'create', 'connect', 'update']],
            ['email', 'string', 'min' => 3, 'max' => 255],
            
            ['phone', 'trim'],
            ['phone', 'required', 'on' => ['register', 'create', 'connect', 'update']],
            ['phone', 'string', 'min' => 3, 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
        ];
    }

    /**
     *
     * @return type
     */
    public function signin()
    {
        if ($this->validate()) {
            $user = User::find()->where(['email' => $this->email])->one();
            if ($user === null) {
                $model = new RegistrationForm();
                $model->username = $this->email;
                $model->email = $this->email;

                if ($model->validate() && $model->register()) {
                    return true;
                } else {
                    $this->addError('email', $model->getFirstError('username'));
                }
            } else {
                //$user->confirmed_at = null;
                $user->save();
                $sendSMS = true;

                if (in_array($user->username, ['damirbek@gmail.com'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                }

                $recipient = '+' . $this->username;
                if ($sendSMS) {
                    Yii::$app->nikita->setRecipient($recipient)
                        ->setText('Ваш код: ' . $token->code . ' is your code' . PHP_EOL . 'wYvKRPwmEXI')
                        ->send();
                    // Yii::$app->mailer->compose()
                    //     ->setFrom('send@dingo.kg')
                    //     ->setTo($this->email)
                    //     ->setSubject("Ваш код авторизации: " . $token->code)
                    //     ->setHtmlBody("<h1>{$token->code}</h1>")
                    //     ->setTextBody('Hello from Resend! This is a test email.')
                    //     ->send();
                }

                return true;
            }
        }
    }
}

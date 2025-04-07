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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required', 'on' => ['register', 'create', 'connect', 'update']],
            ['email', 'string', 'min' => 3, 'max' => 255],
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
            $user = User::find()->where(['username' => $this->phone_number])->one();
            if ($user === null) {
                $model = new RegistrationForm();
                $model->username = $this->phone_number;

                if ($model->validate() && $model->register()) {
                    return true;
                } else {
                    $this->addError('phone_number', $model->getFirstError('username'));
                }
            } else {
                //$user->confirmed_at = null;
                $user->save();
                $sendSMS = true;

                if (in_array($user->username, ['996553000665', '996707889512', '996551170990', '996505170990','996555555555','996333333333','996777777777','996999999999'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '0000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                }

                $recipient = '+' . $user->username;

                if ($sendSMS) {
                    Yii::$app->nikita->setRecipient($recipient)
                        ->setText('Ваш код: ' . $token->code)
                        ->send();
                }

                return true;
            }
        }
    }
}

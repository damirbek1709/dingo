<?php
namespace app\models\user;

use dektrium\user\models\User as BaseUser;
use dektrium\user\helpers\Password;

class User extends BaseUser
{

    const FLAG_DELETED = 1;
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'][] = 'search_data';
        $scenarios['update'][] = 'search_data';
        $scenarios['register'][] = 'search_data';

        $scenarios['create'][] = 'name';
        $scenarios['update'][] = 'name';
        $scenarios['register'][] = 'name';

        $scenarios['create'][] = 'phone';
        $scenarios['update'][] = 'phone';
        $scenarios['register'][] = 'phone';
        return $scenarios;
    }


    public function rules()
    {
        $rules = parent::rules();
        // add some rules

        $rules['search_dataSafe'] = ['search_data', 'safe'];
        $rules['nameSafe'] = ['name', 'safe'];
        $rules['phoneSafe'] = ['phone', 'safe'];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('user', 'Username'),
            'email' => \Yii::t('user', 'E-mail'),
            'name' => \Yii::t('user', 'Имя и фамилия'),
            'phone' => \Yii::t('user', 'Телефон'),
            'registration_ip' => \Yii::t('user', 'Registration ip'),
            'unconfirmed_email' => \Yii::t('user', 'New email'),
            'password' => \Yii::t('user', 'Password'),
            'created_at' => \Yii::t('user', 'Registration time'),
            'last_login_at' => \Yii::t('user', 'Last login'),
            'confirmed_at' => \Yii::t('user', 'Confirmation time'),
        ];
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::findOne(['auth_key' => $token]);
        if ($user !== null && !$user->isBlocked && $user->isConfirmed) {
            return $user;
        }

        return null;
    }

    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $transaction = $this->getDb()->beginTransaction();

        try {
            $this->confirmed_at = $this->module->enableConfirmation ? null : time();
            $this->password = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_REGISTER);

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            if ($this->module->enableConfirmation) {
                /** @var Token $token */
                $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $this);

            }

            //$this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
            $this->trigger(self::AFTER_REGISTER);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }


}
?>
<?php
namespace app\models\user;

use dektrium\user\models\User as BaseUser;
use dektrium\user\helpers\Password;

class User extends BaseUser
{

    
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'][]   = 'search_data';
        $scenarios['update'][]   = 'search_data';
        $scenarios['register'][] = 'search_data';
        return $scenarios;
    }


    public function rules()
    {
        $rules = parent::rules();
        // add some rules

        $rules['search_dataSafe'] = ['search_data', 'safe'];
        
        return $rules;
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
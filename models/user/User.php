<?php
namespace app\models\user;

use dektrium\user\models\User as BaseUser;
use dektrium\user\helpers\Password;
use yii\data\ActiveDataProvider;
use dektrium\rbac\models\Assignment;

class User extends BaseUser
{
    const FIXED_FEE = 10;
    public $objects;


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

       $scenarios['update'][] = 'fee_percent';
        return $scenarios;
    }


    public function rules()
    {
        $rules = parent::rules();
        // add some rules

        $rules['search_dataSafe'] = ['search_data', 'safe'];
        $rules['nameSafe'] = ['name', 'safe'];
        $rules['phoneSafe'] = ['phone', 'safe'];
        $rules['fee_percentSafe'] = ['fee_percent', 'safe'];
        $rules['objectsSafe'] = ['objects', 'safe'];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('user', 'Username'),
            'email' => \Yii::t('user', 'E-mail'),
            'name' => \Yii::t('user', 'Имя и фамилия'),
            'objects' => \Yii::t('user', 'Объекты'),
            'phone' => \Yii::t('user', 'Телефон'),
            'fee_percent' => \Yii::t('user', 'Процент от брони'),
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

    public function getAuthAssignments()
    {
        return $this->hasMany(Assignment::className(), ['user_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }

    public function search($params)
    {
        $query = $this->finder->getUserQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $modelClass = $query->modelClass;
        $table_name = $modelClass::tableName();

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', $table_name . '.created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere(['like', $table_name . '.username', $this->username])
            ->andFilterWhere(['like', $table_name . '.email', $this->email])
            ->andFilterWhere(['like', $table_name . '.name', $this->name])
            ->andFilterWhere(['like', $table_name . '.phone', $this->name])
            ->andFilterWhere([$table_name . '.id' => $this->id])
            ->andFilterWhere([$table_name . 'registration_ip' => $this->registration_ip]);

        return $dataProvider;
    }


}
?>
<?php

namespace app\models\user;

use Yii;
use app\models\user\User;
use app\models\user\RegistrationForm;
use app\models\user\RequireOneValidator;

/**
 * @property int $phone_number Номер телефона
 */
class SigninForm extends \yii\base\Model
{
    public $email;
    public $phone;
    public $dummy;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'phone'], 'trim'],
            [
                ['email', 'phone'],
                function ($attribute, $params, $validator) {
                    if (empty($this->email) && empty($this->phone)) {
                        $this->addError('email', 'Either email or phone must be provided.');
                        $this->addError('phone', 'Either email or phone must be provided.');
                    }
                },
                'skipOnEmpty' => false,
                'skipOnError' => false
            ],
            ['email', 'string', 'min' => 3, 'max' => 255, 'skipOnEmpty' => true],
            ['phone', 'string', 'min' => 3, 'max' => 255, 'skipOnEmpty' => true],
            ['email', 'email', 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'phone' => Yii::t('app', 'Телефон'),
        ];
    }

    /**
     *
     * @return type
     */

}

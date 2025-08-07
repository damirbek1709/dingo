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
            [['email', 'phone'], 'trim'],
            [['email', 'phone'], 'string', 'min' => 3, 'max' => 255],
            ['email', 'email'], // для доп. валидации email

            // Кастомное правило: хотя бы одно поле должно быть заполнено
            [['email', 'phone'], 'validateContact', 'on' => ['signin', 'register', 'create', 'connect', 'update']],
        ];
    }

    public function validateContact($attribute, $params)
    {
        if (empty($this->email) && empty($this->phone)) {
            $this->addError('email', 'Укажите email или номер телефона');
            $this->addError('phone', 'Укажите email или номер телефона');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'phone' => Yii::t('app','Телефон'),
        ];
    }

    /**
     *
     * @return type
     */
    
}

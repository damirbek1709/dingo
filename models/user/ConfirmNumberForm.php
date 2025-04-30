<?php

namespace app\models\user;

use Yii;
use app\models\user\Token;
use yii\web\Response;

/**
 * @property int $confirmation_code Проверочный код
 */
class ConfirmNumberForm extends \yii\base\Model
{
    public $confirmation_code;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['confirmation_code'], 'required'],
            [['confirmation_code'], 'integer'],
            [['confirmation_code'], 'validateConfirmationCode'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'confirmation_code' => 'Проверочный код',
        ];
    }

    /**
     *
     * @return type
     */
    

    public function validateConfirmationCode($attribute, $params)
    {
        $token = Token::find()->where(['code' => $this->$attribute, 'type' => Token::TYPE_CONFIRMATION])->one();
        if ($token === null || $token->isExpired || $token->user === null || !$token)  {
            $this->addError($attribute, 'Проверочный код не найден или устарел');
        }
    }
}

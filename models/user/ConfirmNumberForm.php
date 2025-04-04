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
    public function confirmationCodeFound()
    {
        if ($this->validate()) {
            $token = Token::find()->where(['code' => $this->confirmation_code, 'type' => Token::TYPE_CONFIRMATION])->one();

            if ($token === null || $token->isExpired || $token->user === null) {
                $this->addError('confirmation_code', 'Проверочный код не найден или устарел');
            } else {
                return true;
            }
        }
    }
}

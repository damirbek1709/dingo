<?php
namespace app\models\user;

use Yii;
use app\models\user\Token;

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
     * Validates the confirmation code.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params additional parameters (if any)
     */
    public function validateConfirmationCode($attribute, $params)
    {
        $token = Token::find()->where(['code' => $this->$attribute, 'type' => Token::TYPE_CONFIRMATION])->one();

        if ($token === null || $token->isExpired || $token->user === null) {
            $this->addError($attribute, 'Проверочный код не найден или устарел');
        }
    }
}

?>
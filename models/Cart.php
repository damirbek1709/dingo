<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property string $sessionId
 * @property string|null $cartData
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sessionId'], 'required'],
            [['cartData'], 'string'],
            [['sessionId'], 'string', 'max' => 255],
            [['sessionId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sessionId' => Yii::t('app', 'Session ID'),
            'cartData' => Yii::t('app', 'Cart Data'),
        ];
    }
}

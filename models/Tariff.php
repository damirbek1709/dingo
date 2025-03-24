<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tariff".
 *
 * @property int $id
 * @property int|null $payment_on_book
 * @property int|null $payment_on_reception
 * @property int $cancellation
 * @property int $meal_type
 * @property string $title
 */
class Tariff extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tariff';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_on_book', 'payment_on_reception', 'cancellation', 'meal_type'], 'integer'],
            [['cancellation', 'meal_type', 'title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'payment_on_book' => Yii::t('app', 'Оплата при бронировании'),
            'payment_on_reception' => Yii::t('app', 'Оплата при заселении'),
            'cancellation' => Yii::t('app', 'Отмена'),
            'meal_type' => Yii::t('app', 'Meal Type'),
            'title' => Yii::t('app', 'Title'),
        ];
    }
}

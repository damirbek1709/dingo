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
    const FREE_CANCELLATION_TILL_CHECKIN = 1;
    const FREE_CANCELLATION_WITH_PENALTY = 2;
    const NO_CANCELLATION = 3;

    public $room_list;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tariff';
    }

    public function getCancellationList()
    {
        $arr = [
            self::FREE_CANCELLATION_TILL_CHECKIN => [
                'label' => 'Бесплатная отмена вплоть до времени заезда',
                'hint' => 'В случае отмены бронирования гостю вернётся полная стоимость или предоплата.'
            ],
            self::FREE_CANCELLATION_WITH_PENALTY => [
                'label' => 'Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда',
                'hint' => 'В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. Если бронирование отменено позже указанного времени, вы сможете списать штраф.'
            ],
            self::NO_CANCELLATION => [
                'label' => 'Невозвратный тариф',
                'hint' => 'В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.'
            ]
        ];
        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_on_book', 'payment_on_reception', 'cancellation', 'meal_type', 'object_id'], 'integer'],
            [['cancellation', 'meal_type', 'title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getRoomList($object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $rooms = [];
        if (array_key_exists('rooms', $object)) {
            $rooms = $object['rooms'];
        }
        return $rooms;
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
            'object_id' => Yii::t('app', 'Объект'),
        ];
    }
}

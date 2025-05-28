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

    const MEAL_TYPE_NO_BREAKFEST = 1;
    const MEAL_TYPE_INCLUDED_BREAKFEST = 2;
    const MEAL_TYPE_THREE_TIMES = 3;

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
            self::NO_CANCELLATION => [
                'label' => Yii::t('app', 'Невозвратный тариф'),
                'hint' => Yii::t('app', 'В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.')
            ],
            self::FREE_CANCELLATION_WITH_PENALTY => [
                'label' => Yii::t('app', 'Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда'),
                'hint' => Yii::t('app', 'В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. Если бронирование отменено позже указанного времени, вы сможете списать штраф.')
            ]
        ];
        return $arr;
    }

    public function getCancellationType($type)
    {
        $arr = [
            self::NO_CANCELLATION => [
                'label' => [
                    Yii::t('app', 'Невозвратный тариф'),
                    Yii::t('app', 'Non-refundable tariff'),
                    Yii::t('app', 'Кайтарылбай турган тариф')
                ],
                'hint' => [
                    Yii::t('app', 'В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.'),
                    Yii::t('app', 'In case of cancellation of the reservation, the guest will be charged the full cost of the reservation or the prepayment.'),
                    Yii::t('app', 'Резерв жокко чыгарылган учурда коноктон резервациянын толук наркы же алдын ала төлөм өндүрүлөт.')
                ]
            ],
            self::FREE_CANCELLATION_WITH_PENALTY => [
                'label' => [
                    Yii::t('app', 'Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда'),
                    Yii::t('app', 'Free cancellation, then cancellation with penalty up until check-in time'),
                    Yii::t('app', 'Бекер жокко чыгаруу, андан кийин каттоо убактысына чейин айып менен жокко чыгаруу'),
                ],
                'hint' => [
                    Yii::t('app', 'В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. Если бронирование отменено позже указанного времени, вы сможете списать штраф.'),
                    Yii::t('app', 'If you cancel before the specified time, the cost of the reservation or prepayment will be fully refunded to the guest. If the reservation is cancelled after the specified time, you will be able to write off a penalty.'),
                    Yii::t('app', 'Көрсөтүлгөн мөөнөткө чейин жокко чыгарылган учурда, бронь же алдын ала төлөмдүн баасы конокко толугу менен кайтарылып берилет. Белгиленген убакыттан кийин ээлеп коюуңуз жокко чыгарылса, сизден айып пул алынат.')
                ]
            ]
        ];
        return $arr[$type];
    }

    public static function staticCancellationType($type)
    {
        $arr = [
            self::NO_CANCELLATION => [
                'label' => [
                    Yii::t('app', 'Невозвратный тариф'),
                    Yii::t('app', 'Non-refundable tariff'),
                    Yii::t('app', 'Кайтарылбай турган тариф')
                ],
                'hint' => [
                    Yii::t('app', 'В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.'),
                    Yii::t('app', 'In case of cancellation of the reservation, the guest will be charged the full cost of the reservation or the prepayment.'),
                    Yii::t('app', 'Резерв жокко чыгарылган учурда коноктон резервациянын толук наркы же алдын ала төлөм өндүрүлөт.')
                ],
            ],
            self::FREE_CANCELLATION_WITH_PENALTY => [
                'label' => [
                    Yii::t('app', 'Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда'),
                    Yii::t('app', 'Free cancellation, then cancellation with penalty up until check-in time'),
                    Yii::t('app', 'Бекер жокко чыгаруу, андан кийин каттоо убактысына чейин айып менен жокко чыгаруу'),
                ],
                'hint' => [
                    Yii::t('app', 'В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. Если бронирование отменено позже указанного времени, вы сможете списать штраф.'),
                    Yii::t('app', 'If you cancel before the specified time, the cost of the reservation or prepayment will be fully refunded to the guest. If the reservation is cancelled after the specified time, you will be able to write off a penalty.'),
                    Yii::t('app', 'Көрсөтүлгөн мөөнөткө чейин жокко чыгарылган учурда, бронь же алдын ала төлөмдүн баасы конокко толугу менен кайтарылып берилет. Белгиленген убакыттан кийин ээлеп коюуңуз жокко чыгарылса, сизден айып пул алынат.')
                ]
            ]
        ];
        return $arr[$type];
    }

    public function getMealList()
    {
        $arr = [
            self::MEAL_TYPE_NO_BREAKFEST => [
                'label' => Yii::t('app', 'Завтрак не включен'),
            ],
            self::MEAL_TYPE_INCLUDED_BREAKFEST => [
                'label' => Yii::t('app', 'Завтрак включен'),
            ],
            self::MEAL_TYPE_THREE_TIMES => [
                'label' => Yii::t('app', 'Трехразовое питание'),
            ]
        ];
        return $arr;
    }

    public function getCancellationTitle($id)
    {
        $arr = [
            self::NO_CANCELLATION => [
                'label' => 'Невозвратный тариф',
                'class' => 'no-return-tariff',
                'hint' => 'В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.'
            ],
            self::FREE_CANCELLATION_WITH_PENALTY => [
                'label' => 'Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда',
                'class' => 'with-penalty-tariff',
                'hint' => 'В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. Если бронирование отменено позже указанного времени, вы сможете списать штраф.'
            ]
        ];
        return $arr[$id];
    }

    public function isTariffBinded($arr)
    {
        foreach ($arr as $item) {
            if ($item['id'] == $this->id) {
                //echo "yes {$item['id']}";die();
                return true;
            }
        }
        return false;
    }

    

    public function getMealTitle($id)
    {
        $result = Vocabulary::find()->where(['id' => $id])->one();
        return ['label'=>$result->title, 'class'=>'included-breakfast'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_on_book', 'payment_on_reception', 'cancellation', 'meal_type', 'object_id', 'penalty_days'], 'integer'],
            [['cancellation', 'meal_type', 'title', 'title_en', 'title_ky'], 'required'],
            [['penalty_sum'], 'number'],
            [['room_list'], 'safe'],
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
            'payment_on_book' => Yii::t('app', 'Оплата онлайн во время бронирования'),
            'payment_on_reception' => Yii::t('app', 'Оплата при заселении'),
            'cancellation' => Yii::t('app', 'Отмена'),
            'meal_type' => Yii::t('app', 'Meal Type'),
            'title' => Yii::t('app', 'Title'),
            'object_id' => Yii::t('app', 'Объект'),
            'penalty_sum' => Yii::t('app', 'Размер штрафа (% от стоимости)'),
            'penalty_days' => Yii::t('app', 'Количество дней до заезда'),
        ];
    }
}

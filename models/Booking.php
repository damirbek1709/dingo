<?php

namespace app\models;

use Yii;
use app\components\flashpay\Payment;
use app\components\flashpay\Gate;
use IntlDateFormatter;
use DateTime;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "booking".
 *
 * @property int $id
 * @property int $object_id
 * @property int $room_id
 * @property string $tariff_id
 * @property float $sum
 * @property string|null $guest_email
 * @property string|null $guest_phone
 * @property string|null $guest_name
 * @property string|null $speacial_comment
 * @property string $date_from
 * @property string $date_to
 * @property int $status
 * @property string|null $other_guests
 * @property string|null $transaction_number
 * @property int|null $cancellation_type
 * @property float|null $cancellation_penalty_sum
 */
class Booking extends \yii\db\ActiveRecord
{

    const MERCHANT_ID = '144631';
    const SECRET_KEY = '0bf1531b6df88949c9dd24115d6affc67fa04005bc4105f65ac373edbc89a42ab51943838f4552777f3b1d08278d7df144280edc80cd4613d38d4e2256a3601e';

    const PAID_STATUS_NOT_PAID = 0;
    const PAID_STATUS_PAID = 1;
    const PAID_STATUS_CANCELED = 2;
    const PAID_STATUS_CANCEL_INQUIRY = 3;

    const CANCEL_REASON_PLANS_CHANGED = 1;
    const CANCEL_REASON_BETTER_OPTION = 2;
    const CANCEL_REASON_UNPREDICTED_SITUATION = 3;
    const CANCEL_REASON_MISTAKE = 4;
    const CANCEL_REASON_NO_RESPONSE = 5;
    const CANCEL_REASON_OTHER = 6;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_id', 'room_id', 'tariff_id', 'sum', 'date_from', 'date_to', 'status', 'owner_id'], 'required'],
            [['object_id', 'room_id', 'status', 'cancellation_type', 'cancel_reason_id'], 'integer'],
            [['status'], 'default', 'value' => 1],
            [['created_at'], 'default', 'value' => date('Y-m-d')],
            [['sum', 'cancellation_penalty_sum'], 'number'],
            [['date_from', 'date_to'], 'safe'],
            [['tariff_id', 'currency'], 'string', 'max' => 11],
            [['guest_email', 'guest_phone', 'guest_name', 'special_comment', 'transaction_number'], 'string', 'max' => 255],
            [['other_guests'], 'string', 'max' => 500],
        ];
    }

    public function getCancelReasonArray()
    {
        $arr = [
            self::CANCEL_REASON_PLANS_CHANGED => [
                'Мои планы изменились',
                'My planse are changed',
                'Менин пландарым өзгөрдү'
            ],
            self::CANCEL_REASON_BETTER_OPTION => [
                'Я нашел более выгодное предложение',
                'I found a better deal',
                'Мен жакшыраак келишим таптым'

            ],

            self::CANCEL_REASON_UNPREDICTED_SITUATION => [
                'Непредвиденная ситуация с поездкой (отмена рейса, проблемы с визой и т. д.)',
                'Unforeseen travel situation (flight cancellation, visa problems, etc.)',
                'Күтүлбөгөн саякат кырдаалы (учууну жокко чыгаруу, виза көйгөйлөрү ж.б.)'
            ],
            self::CANCEL_REASON_MISTAKE => [
                'Я допустил ошибку в датах или деталях бронирования',
                'I made a mistake in the dates or booking details',
                'Даталарда же ээлөө деталдарында ката кетирдим'
            ],
            self::CANCEL_REASON_NO_RESPONSE => [
                'Отель не отвечает на запросы или изменил условия',
                'The hotel does not respond to inquiries or has changed its terms',
                'Мейманкана суроо-талаптарга жооп бербейт же шарттарын өзгөрттү'
            ],
            self::CANCEL_REASON_OTHER => [
                'Другое',
                'Other',
                'Башка'
            ],
        ];

        if ($this->cancel_reason_id) {
            return $arr[$this->cancel_reason_id];
        }
        return null;

    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Объект'),
            'room_id' => Yii::t('app', 'Номер'),
            'tariff_id' => Yii::t('app', 'Тариф'),
            'sum' => Yii::t('app', 'Сумма'),
            'guest_email' => Yii::t('app', 'E-mail гостя'),
            'guest_phone' => Yii::t('app', 'Телефон гостя'),
            'guest_name' => Yii::t('app', 'ФИО'),
            'special_comment' => Yii::t('app', 'Особое пожелание'),
            'date_from' => Yii::t('app', 'Заезд'),
            'date_to' => Yii::t('app', 'Выезд'),
            'status' => Yii::t('app', 'Статус'),
            'other_guests' => Yii::t('app', 'Имена других гостей'),
            'transaction_number' => Yii::t('app', 'Номер транзакции'),
            'cancellation_type' => Yii::t('app', 'Тип отмены'),
            'cancellation_penalty_sum' => Yii::t('app', 'Сумма штрафа'),
            'created_at' => Yii::t('app', 'Дата брони'),
            'owner_id' => Yii::t('app', 'Хост'),
            'cancel_reason_id' => Yii::t('app', 'Причина отмены'),
        ];
    }

    public function fields()
    {
        $parent = parent::fields();

        return ArrayHelper::merge($parent, [
            'objectDetails',
            'cancelReasonArray'
        ]);
    }

    public function getObjectDetails()
    {
        $arr = [];
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $result = $index->getDocument($this->object_id);

        $object = Objects::findOne($this->object_id);
        $room = [];



        $arr['name'] = $result['name'];
        $arr['image'] = $object->getImage()->getUrl('200x');
        $arr['address'] = $result['address'];
        $arr['phone'] = $result['phone'];
        $arr['city'] = $result['city'];
        $arr['totalPrice'] = (float)$this->sum;
        $arr['transaction_number'] = (int)$this->transaction_number;

        $arr['email'] = $result['email'];
        $arr['check_in'] = $result['check_in'];
        $arr['check_out'] = $result['check_out'];
        $arr['is_returnable'] = false;
        $arr['other_guests']=$this->other_guests;

        $tariff_model = Tariff::findOne($this->tariff_id);
        $arr['terms'] = [];
        if (array_key_exists('terms', $result)) {
            $arr['terms'] = $result['terms'];
        }

        if (array_key_exists('rooms', $result)) {
            foreach ($result['rooms'] as $roomData) {
                if ($this->room_id == $roomData['id']) {
                    $room = $roomData;
                    break;
                }
            }
            $arr['room_title'] = $room['room_title'];
            $arr['bed_types'] = $room['bed_types'];
            $tariff = [];
            if (array_key_exists('tariff', $room)) {
                foreach ($room['tariff'] as $tariffData) {
                    if ($this->tariff_id == $tariffData['id']) {
                        $tariff = $tariffData;
                        break;
                    }
                }
                $arr['meal_type'] = $tariff['meal_type'];
                if ($this->cancel_reason_id == Tariff::NO_CANCELLATION) {
                    $arr['is_returnable'] = false;
                    $arr['penalty_days'] = null;
                    $arr['penalty_percent'] = null;
                }
                 elseif ($this->cancel_reason_id == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
                    $arr['penalty_days'] = $tariff_model->penalty_days;
                    $arr['penalty_percent'] = $tariff_model->penalty_sum;
                    // $current_date = date('Y-m-d');
                    // $date_checkin = date('Y-m-d', strtotime($this->date_from));
                    // $penalty_days = $tariff_model->penalty_days;

                    // // Calculate days left until check-in (positive = future, negative = past)
                    // $days_left = (strtotime($date_checkin) - strtotime($current_date)) / (60 * 60 * 24);

                    // if ($days_left >= $penalty_days) {
                    //     $sum_to_return = $this->sum;
                    // } else {
                    //     $sum_to_return = $this->sum - $this->cancellation_penalty_sum;
                    // }
                    // $arr['is_returnable'] = true;
                    // $arr['sum_to_return'] = $sum_to_return;
                    // $arr['days_left'] = $days_left;
                }
            }
        }
        

        return $arr;
    }

    public static function pay($data)
    {
        $booking = Booking::findOne($data['booking_id']);
        $booking->transaction_number = $data['transaction_number'];
        $booking->save(false);

        $payment = new Payment(self::MERCHANT_ID);
        $payment->setPaymentAmount($data['sum'])->setPaymentCurrency($data['currency']);
        $payment->setPaymentId($data['transaction_number']);
        $payment->setCustomerId("customer_" . $data['user_id']);
        $payment->setPaymentDescription('Тестовый платёж');
        $gate = new Gate(self::SECRET_KEY);
        $url = $gate->getPurchasePaymentPageUrl($payment);
        return $url;
    }

    public function bookingRoomTitle()
    {
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($this->object_id);
        $room_title = "";

        if (array_key_exists('rooms', $object)) {
            $roomData = [];
            foreach ($object['rooms'] as $room) {
                if ($room['id'] == $this->room_id) {
                    $room_title = $room['room_title'][0];
                    break;
                }
            }
        }
        return $room_title;
    }

    public static function getRoomList($object_id)
    {
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);
        $room_title = "";

        $lang_index = 0;
        switch (Yii::$app->language) {
            case 'ru':
                $lang_index = 0;
                break;
            case 'en':
                $lang_index = 1;
                break;
            case 'ky':
                $lang_index = 2;
                break;
            default:
                $lang_index = 0;
        }

        $roomData = [];

        if (array_key_exists('rooms', $object)) {
            
            $tariffData = [];
            foreach ($object['rooms'] as $room) {
                $roomData[$room['id']] = $room['room_title'][$lang_index];
                if (array_key_exists('tariff', $room)) {
                    foreach ($room['tariff'] as $tariff) {
                        $tariffData[$tariff['id']] = $tariff['title'][$lang_index];
                    }
                }
            }
        }
        return $roomData;
    }

    public static function tariffList($object_id, $room_id)
    {
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);

        $lang_index = 0;
        switch (Yii::$app->language) {
            case 'ru':
                $lang_index = 0;
                break;
            case 'en':
                $lang_index = 1;
                break;
            case 'ky':
                $lang_index = 2;
                break;
            default:
                $lang_index = 0;
        }
        $roomData = [];
        $tariffData = [];

        if (array_key_exists('rooms', $object)) {
            foreach ($object['rooms'] as $room) {
                if ($room['id'] == $room_id) {
                    $roomData = $room;
                    break;
                }
            }

            if (array_key_exists('tariff', $roomData)) {
                foreach ($room['tariff'] as $tariff) {
                    $tariffData[$tariff['id']] = $tariff['title'][$lang_index];
                }
            }
        }

        return $tariffData;
    }



    public function bookingTariffTitle()
    {
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($this->object_id);
        $tariff_title = "";

        if (array_key_exists('rooms', $object)) {
            $roomData = [];
            foreach ($object['rooms'] as $room) {
                if ($room['id'] == $this->room_id) {
                    $roomData = $room;
                    break;
                }
            }

            if (array_key_exists('tariff', $roomData)) {
                foreach ($roomData['tariff'] as $tariff) {
                    if ($tariff['id'] == $this->tariff_id) {
                        $tariff_title = $tariff['title'][0];
                        break;
                    }
                }
            }
        }
        return $tariff_title;
    }

    public function bookingObjectTitle()
    {
        $object_title = "";
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($this->object_id);
        if ($object)
            $object_title = $object['name'][0];
        return $object_title;
    }
    public function bookingStatusString()
    {
        $string = Yii::t('app', 'Активный');
        if ($this->status == self::PAID_STATUS_PAID) {
            if ($this->date_from <= date('Y-m-d')) {
                $string = Yii::t('app', 'Активный');
            } elseif ($this->date_to > date('Y-m-d')) {
                $string = Yii::t('app', 'Завершен');
            }
        } elseif ($this->status == self::PAID_STATUS_CANCELED) {
            $string = Yii::t('app', 'Отменен');
        } elseif ($this->status == self::PAID_STATUS_CANCEL_INQUIRY) {
            $string = Yii::t('app', 'Заявка на отмену брони');
        }
        return $string;
    }

    public function dateFormat($date)
    {
        if (!$date)
            $date = date('Y-m-d');
        $formatter = new IntlDateFormatter(
            'ru_RU', // Russian locale
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            'Europe/Moscow', // Optional: timezone
            null,
            'd MMMM yyyy' // Format: day full_month_name year
        );
        return $formatter->format(new DateTime($date));
    }

}

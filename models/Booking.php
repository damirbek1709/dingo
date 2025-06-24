<?php

namespace app\models;

use app\models\user\User;
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

    public $commission;
    public $income;

    public $refund_status;
    public $refund_request_date;
    public $date_range;
    public $payment_type;
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
            [['date_from', 'date_to', 'cancel_date', 'comission', 'income'], 'safe'],
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
                'My plans are changed',
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
            'created_date' => Yii::t('app', 'Дата отмены'),
            'owner_id' => Yii::t('app', 'Хост'),
            'comission' => Yii::t('app', 'Комиссия'),
            'income' => Yii::t('app', 'Прибыль'),
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

    public function comissionFee()
    {
        $percent = Yii::$app->user->identity->fee_percent ? Yii::$app->user->identity->fee_percent : User::FIXED_FEE;
        $percent_sum = $this->sum * $percent / 100;
        return $percent_sum . " " . $this->currency;
    }

    public function incomeString()
    {
        $percent = Yii::$app->user->identity->fee_percent ? Yii::$app->user->identity->fee_percent : User::FIXED_FEE;
        $percent_sum = $this->sum * $percent / 100;
        $income_sum = $this->sum - $percent_sum;
        return $income_sum . " " . $this->currency;
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
        $arr['totalPrice'] = (float) $this->sum;
        $arr['transaction_number'] = (int) $this->transaction_number;

        $arr['email'] = $result['email'];
        $arr['check_in'] = $result['check_in'];
        $arr['check_out'] = $result['check_out'];
        $arr['is_returnable'] = false;
        $arr['other_guests'] = $this->other_guests;

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
                } elseif ($this->cancel_reason_id == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
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

    public function bookingOwnerTitle()
    {
        $user = User::findOne($this->owner_id);
        if ($user->name) {
            return $user->name;
        }
        return $user->username;
    }

    public function getTariff()
    {
        $rel = $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
        if ($rel->exists()) {
            return $rel;
        }
        return null;
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
        $color = '#52c41a';

        if ($this->date_from <= date('Y-m-d') && $this->date_to >= date('Y-m-d')) {
            $string = Yii::t('app', 'Активный');
            $color = '#52c41a';
        }

        if ($this->date_to < date('Y-m-d')) {
            $string = Yii::t('app', 'Завершен');
            $color = '#000000e0';
        }

        if ($this->date_from > date('Y-m-d')) {
            $string = Yii::t('app', 'В ожидании');
            $color = '#fa8c16';

        }

        if ($this->status == self::PAID_STATUS_CANCELED) {
            $string = Yii::t('app', 'Отменен');
            $color = '#f5222d';
        }
        return ["string" => $string, "color" => $color];
    }

    public function cancelText()
    {
        $text = "Отмена не возможна";
        $tariff = Tariff::findOne($this->tariff_id);
        if ($tariff && $tariff->cancellation == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
            $penalty_days = $tariff->penalty_days;
            $date_free_till = date("Y-m-d", strtotime($this->date_from . " -{$penalty_days} days"));
            $date_free_till = $this->dateFormat($date_free_till);
            $text = "Бесплатная отмена до {$date_free_till}";
        }
        return $text;
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

    public function guestAmount()
    {
        if (empty($this->other_guests) || trim($this->other_guests) === '') {
            return 1;
        }

        // Split by comma and count non-empty names after trimming whitespace
        $names = array_map('trim', explode(',', $this->other_guests));
        $names = array_filter($names, function ($name) {
            return !empty($name);
        });

        return count($names) + 1;
    }

    public static function generateSignature($data)
    {
        // Step 1: Remove signature field if it exists
        if (isset($data['signature'])) {
            unset($data['signature']);
        }

        // Step 2: Convert data to key:value strings and handle boolean values  
        $keyValuePairs = [];
        foreach ($data as $key => $value) {
            // Convert boolean values to 1/0
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            // Convert to UTF-8 string
            $key = mb_convert_encoding($key, 'UTF-8');
            $value = mb_convert_encoding((string) $value, 'UTF-8');

            $keyValuePairs[] = $key . ':' . $value;
        }

        // Step 3: Sort in natural order (case-insensitive, natural sorting)
        natsort($keyValuePairs);

        // Step 4: Join with semicolon delimiter
        $signatureString = implode(';', $keyValuePairs);

        // Step 5: Calculate HMAC-SHA512 and encode with Base64
        $hmac = hash_hmac('sha512', $signatureString, self::SECRET_KEY, true);
        $signature = base64_encode($hmac);

        return $signature;
    }

    /**
     * Generate signature for refund request
     */
    public function generateRefundSignature($refundData)
    {
        return $this->generateSignature($refundData);
    }

    /**
     * Verify received signature
     */
    public function verifySignature($data, $receivedSignature)
    {
        $calculatedSignature = $this->generateSignature($data);
        return hash_equals($calculatedSignature, $receivedSignature);
    }

}

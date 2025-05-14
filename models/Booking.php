<?php

namespace app\models;

use Yii;
use app\components\flashpay\Payment;
use app\components\flashpay\Gate;

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
            [['object_id', 'room_id', 'tariff_id', 'sum', 'date_from', 'date_to', 'status'], 'required'],
            [['object_id', 'room_id', 'status', 'cancellation_type'], 'integer'],
            [['status'], 'default', 'value' => 1],
            [['sum', 'cancellation_penalty_sum'], 'number'],
            [['date_from', 'date_to'], 'safe'],
            [['tariff_id'], 'string', 'max' => 11],
            [['guest_email', 'guest_phone', 'guest_name', 'special_comment', 'transaction_number'], 'string', 'max' => 255],
            [['other_guests'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'room_id' => Yii::t('app', 'Room ID'),
            'tariff_id' => Yii::t('app', 'Tariff ID'),
            'sum' => Yii::t('app', 'Sum'),
            'guest_email' => Yii::t('app', 'Guest Email'),
            'guest_phone' => Yii::t('app', 'Guest Phone'),
            'guest_name' => Yii::t('app', 'Guest Name'),
            'special_comment' => Yii::t('app', 'Special Comment'),
            'date_from' => Yii::t('app', 'Date From'),
            'date_to' => Yii::t('app', 'Date To'),
            'status' => Yii::t('app', 'Status'),
            'other_guests' => Yii::t('app', 'Other Guests'),
            'transaction_number' => Yii::t('app', 'Transaction Number'),
            'cancellation_type' => Yii::t('app', 'Cancellation Type'),
            'cancellation_penalty_sum' => Yii::t('app', 'Cancellation Penalty Sum'),
        ];
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
                    $room_title = $room['room_title'];
                    break;
                }
            }
        }
        return $room_title;
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
                        $tariff_title = $tariff['title'];
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

}

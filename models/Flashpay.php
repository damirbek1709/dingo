<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;
use app\components\flashpay\Payment;
use app\components\flashpay\Gate;

/**
 * This is the model class for table "freedom".
 *
 * @property int $id
 * @property int|null $amount
 * @property string|null $currency
 * @property string|null $name
 * @property string|null $email
 * @property string|null $pg_salt
 * @property string|null $pg_sig
 * @property string|null $pg_payment_method
 * @property string|null $pg_payment_id
 * @property string|null $pg_status
 * @property string|null $pg_transaction_status
 * @property string|null $pg_testing_mode
 * @property int|null $pg_captured
 * @property string|null $pg_card_pan
 * @property float|null $pg_net_amount
 * @property string|null $pg_card_owner
 * @property string|null $pg_user_phone
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Flashpay extends \yii\db\ActiveRecord
{
    // const MERCHANT_ID = '553057';
    // const SECRET_KEY = 'BFt1v9erN5QCTlWR';
    protected static $site = 'https://partner.dingo.kg/site';

    // const MERCHANT_ID = '144631';
    // const SECRET_KEY = '0bf1531b6df88949c9dd24115d6affc67fa04005bc4105f65ac373edbc89a42ab51943838f4552777f3b1d08278d7df144280edc80cd4613d38d4e2256a3601e';

    const MERCHANT_ID = '147713';
    const SECRET_KEY = '18d8c2073556c0457500bfe1e8de78a35d853e2fa4a10519f3993c84197e709e9b807b0291e7904b53762c5dd9492aa9330c4f6a72d84b9069250b529ecd28f2';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'freedom';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'created_at', 'updated_at', 'pg_captured', 'order_id'], 'integer'],
            [['pg_net_amount'], 'number'],
            [['currency', 'pg_payment_method', 'pg_payment_id', 'pg_status', 'pg_transaction_status', 'pg_testing_mode', 'pg_card_pan'], 'string', 'max' => 20],
            [['name', 'email', 'pg_salt', 'pg_sig', 'pg_user_phone', 'pg_card_owner',], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'name' => 'Name',
            'email' => 'Email',
            'pg_salt' => 'Salt',
            'pg_sig' => 'Sig',
            'pg_payment_method' => 'Payment Method',
            'pg_payment_id' => 'Payment ID',
            'pg_status' => 'Status',
            'pg_transaction_status' => 'Transaction Status',
            'pg_testing_mode' => 'Testing Mode',
            'pg_captured' => 'Captured',
            'pg_card_pan' => 'Card Pan',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'pg_net_amount' => 'Net Amount',
            'pg_card_owner' => 'Card Owner',
            'pg_user_phone' => 'Phone',
        ];
    }

    /**
     * params 
     * default_sum
     * donation_sum
     * currency
     * name
     * email
     */

    public static function pay()
    {
        $currency = 'KGS';
        $payment = new Payment(self::MERCHANT_ID);
        // Идентификатор проекта, полученный от Flashpay при интеграции

        $payment->setPaymentAmount(31415)->setPaymentCurrency('KGS');
        // Сумма (в дробных единицах валюты) и код валюты (в формате ISO-4217 alpha-3)

        $payment->setPaymentId('1539435672324');
        // Идентификатор платежа, уникальный в рамках проекта

        $payment->setCustomerId('customer_' . Yii::$app->user->id);
        // Идентификатор пользователя в рамках проекта

        $payment->setPaymentDescription('Тестовый платёж');
        // Описание платежа. Не обязательный, но полезный параметр

        $gate = new Gate(self::SECRET_KEY);
        // Секретный ключ проекта, полученный от Flashpay

        /* Получение URL для вызова платёжной формы */
        $url = $gate->getPurchasePaymentPageUrl($payment);
        return $url;
    }
}

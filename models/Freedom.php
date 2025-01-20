<?php

namespace app\models;

use Yii;
use yii\httpclient\Client;

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
class Freedom extends \yii\db\ActiveRecord
{
    // const MERCHANT_ID = '553057';
    // const SECRET_KEY = 'BFt1v9erN5QCTlWR';
    protected static $site = 'https://dilbar.style/site';

    const MERCHANT_ID = '553057';
    const SECRET_KEY = 'BFt1v9erN5QCTlWR';


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

    public static function pay($params)
    {
        $currency = 'KGS';
        $sum = $params['default_sum'];

        $freedom = new Freedom();
        $freedom->amount = $sum;
        $freedom->pg_net_amount = $sum;
        $freedom->currency = $currency;
        $freedom->order_id = $params['order_id'];
        if (!empty ($params['name'])) {
            $freedom->name = $params['name'];
        }
        if (!empty ($params['email'])) {
            $freedom->email = $params['email'];
        }
        if (!empty ($params['phone'])) {
            $freedom->pg_user_phone = $params['phone'];
        }
        if (!empty ($params['name'])) {
            $freedom->pg_card_owner = $params['name'];
        }
        $freedom->save();

        $req = self::generateSalt(['order_id' => $freedom->id, 'amount' => $sum, 'currency' => $currency, 'email' => $freedom->email]);


        $freedom->pg_salt = $req['pg_salt'];
        $freedom->save();
        $pay = self::freedomPay($req);
        $freedom->pg_status = $pay['pg_status'];
        //$freedom->pg_transaction_status = $pay['pg_result'];
        $freedom->pg_payment_id = $pay['pg_payment_id'];
        $freedom->pg_sig = $pay['pg_sig'];
        $freedom->save();
        return $pay['pg_redirect_url'];
    }

    protected static function generateRevokeSalt($params)
    {
        $request = $requestForSignature = [
            'pg_merchant_id' => self::MERCHANT_ID,
            'pg_payment_id' => $params['pg_payment_id'],
            'pg_refund_amount' => $params['pg_refund_amount'],
            'pg_salt' => $params['pg_salt'],
            'pg_order_id' => $params['pg_order_id'],
        ];

        // Превращаем объект запроса в плоский массив
        $requestForSignature = self::makeFlatParamsArray($requestForSignature);

        // Генерация подписи
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'revoke.php'); // Добавление в начало имени скрипта
        array_push($requestForSignature, Freedom::SECRET_KEY); // Добавление в конец секретного ключа

        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
        return $request;
    }


    protected static function generateSalt($params = ['order_id' => 4, 'amount' => 21, 'currency' => 'KGS', 'email' => null])
    {
        $request = $requestForSignature = [
            'pg_order_id' => $params['order_id'],
            'pg_merchant_id' => Freedom::MERCHANT_ID,
            'pg_amount' => $params['amount'],
            'pg_description' => 'Dilbar Home',
            'pg_salt' => base64_encode(random_bytes(10)),
            'pg_currency' => $params['currency'],
            'pg_check_url' => self::$site . '/freedom-check',
            'pg_result_url' => self::$site . '/freedom-result',
            'pg_request_method' => 'POST',
            'pg_success_url' => self::$site . '/thankyou',
            'pg_failure_url' => self::$site . '/payment-failure',
            'pg_success_url_method' => 'GET',
            'pg_failure_url_method' => 'GET',
            'pg_state_url' => self::$site . '/freedom-state',
            'pg_state_url_method' => 'GET',
            'pg_site_url' => self::$site . '/',
            'pg_language' => 'ru',
        ];

        if (!empty ($params['email'])) {
            $requestForSignature['pg_user_contact_email'] = $params['email'];
            $request['pg_user_contact_email'] = $params['email'];
        }


        // Превращаем объект запроса в плоский массив
        $requestForSignature = self::makeFlatParamsArray($requestForSignature);

        // Генерация подписи
        ksort($requestForSignature); // Сортировка по ключю
        array_unshift($requestForSignature, 'init_payment.php'); // Добавление в начало имени скрипта
        array_push($requestForSignature, Freedom::SECRET_KEY); // Добавление в конец секретного ключа

        $request['pg_sig'] = md5(implode(';', $requestForSignature)); // Полученная подпись
        return $request;
    }

    public static function revoke($order_id)
    {
        $freedom = Freedom::find()->where(['order_id' => $order_id])->one();
        //echo $freedom->pg_payment_id;die();
        $req = self::generateRevokeSalt([
            'pg_merchant_id' => self::MERCHANT_ID,
            'pg_payment_id' => $freedom->pg_payment_id,
            'pg_refund_amount' => $freedom->amount,
            'pg_salt' => $freedom->pg_salt,
            'pg_order_id'=>$order_id
        ]);
        //echo "<pre>";print_r($req);echo "</pre>";die();
        return self::freedomRevoke($req);
        
    }

    protected static function freedomRevoke($request)
    {
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.freedompay.kg/revoke.php')
            ->setData($request)
            ->send();
        echo "<pre>";print_r($response);echo "</pre>";die();
        return $response->data;
    }


    protected static function freedomPay($request)
    {
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.freedompay.kg/init_payment.php')
            //https://api.freedompay.kg/v5/sdk/tokenize
            ->setData($request)
            ->send();
        return $response->data;
    }
    /**
     * Функция превращает многомерный массив в плоский
     */
    protected static function makeFlatParamsArray($arrParams, $parent_name = '')
    {
        $arrFlatParams = [];
        $i = 0;
        foreach ($arrParams as $key => $val) {
            $i++;
            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = array_merge($arrFlatParams, self::makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string) $val);
        }

        return $arrFlatParams;
    }
}

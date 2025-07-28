<?php

namespace app\modules\admin\controllers;


use app\models\Booking;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\admin\controllers\actions\FlashPayPayoutAction;
use yii\helpers\Json;

class PaymentController extends Controller
{
    /**
     * Declare actions
     */
    public function actions()
    {
        return [
            'flashpay-payout' => [
                'class' => FlashPayPayoutAction::class,
                'projectId' => Yii::$app->params['flashpay']['project_id'] ?? null,
                'secretKey' => Yii::$app->params['flashpay']['secret_key'] ?? null,
                'apiUrl' => Yii::$app->params['flashpay']['api_url'] ?? 'https://gateway.flashpay.kg/v2/payment/individual/payout',
                'timeout' => 30,
            ],
        ];
    }

    /**
     * Index action - display payment form
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Test payout action - for development/testing purposes
     */
    public function actionTestPayout()
    {
        // Sample test data
        $testData = [
            'payment_id' => 'test_payout_' . time(),
            'card' => [
                'pan' => '4169585343246905', // Test card number
                'year' => 2026,
                'month' => 07,
                'card_holder' => 'DAMIRBEK SYDYKOV',
                'cvv' => '617'
            ],
            'customer' => [
                'id' => 'customer_' . time(),
                'first_name' => 'DAMIRBEK',
                'last_name' => 'SYDYKOV',
                'email' => 'damirbek@gmail.com',
                'phone' => '996551170990',
                'country' => 'KG',
                'city' => 'Bishkek'
            ],
            'payment' => [
                'amount' => 1, // 100.00 in minor units
                'currency' => 'KGS',
                'description' => 'Test payout payment'
            ]
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // Set request data for testing
        Yii::$app->request->setBodyParams($testData);

        // Call the payout action
        return $this->runAction('flashpay-payout');
    }

    public function actionCardPayout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $url = 'https://gateway.flashpay.kg/v2/payment/card/payout';

        $data = [
            "general" => [
                "project_id" => Booking::MERCHANT_ID,
                "payment_id" => time(),
                "terminal_callback_url" => "https://yourdomain.com/callback",
                "referrer_url" => "https://yourdomain.com",
                "merchant_callback_url" => "https://yourdomain.com/merchant-callback",
            ],
            "card" => [
                "pan" => "4169585343246905",
                "year" => 2026,
                "month" => 7,
                "issue_year" => 2021,
                "issue_month" => 7,
                "card_holder" => "DAMIRBEK SYDYKOV",
                "cvv" => "617",
                "save" => true,
                "stored_card_type" => 0,
            ],
            "customer" => [
                "id" => "customer123",
                "email" => "user@example.com",
                "ip_address" => Yii::$app->request->userIP,
                "gender" => "male",
                // Fill other necessary fields or leave optional ones
            ],
            "sender" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "phone" => "+996700000000",
                // ...
            ],
            "recipient" => [
                "country" => "KG",
                "city" => "Bishkek",
                "first_name" => "Jane",
                "last_name" => "Doe",
            ],
            "payment" => [
                "amount" => 1,
                "currency" => "KGS",
                "description" => "Test payout",
                "is_fast" => true,
                // ...
            ],
            // Add other optional sections if needed
        ];

        $signature = $this->generateSignature($data);
        $data['general']['signature'] = $signature;



        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($data));

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        return [
            'success' => true,
            'status' => $statusCode,
            'response' => Json::decode($response),
        ];
    }

    private function convertToParameterStrings($data, $parentPath = '')
    {
        $strings = [];

        foreach ($data as $key => $value) {
            $currentPath = $parentPath ? $parentPath . ':' . $key : $key;

            if (is_array($value)) {
                // Handle arrays with numeric indices
                if (array_keys($value) === range(0, count($value) - 1)) {
                    // Sequential array
                    foreach ($value as $index => $item) {
                        if (is_array($item)) {
                            $strings = array_merge($strings, $this->convertToParameterStrings($item, $currentPath . ':' . $index));
                        } else {
                            $strings[] = $currentPath . ':' . $index . ':' . $this->formatValue($item);
                        }
                    }
                } else {
                    // Associative array
                    $strings = array_merge($strings, $this->convertToParameterStrings($value, $currentPath));
                }
            } else {
                $strings[] = $currentPath . ':' . $this->formatValue($value);
            }
        }

        return $strings;
    }

    private function formatValue($value)
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return '';
        }

        if ($value === '') {
            return '';
        }

        return (string) $value;
    }

    private function generateSignature($data)
    {
        // Step 1: Remove signature parameter if it exists
        $signData = $data;
        if (isset($signData['general']['signature'])) {
            unset($signData['general']['signature']);
        }

        // Step 2: Convert to parameter strings with full path notation
        $paramStrings = $this->convertToParameterStrings($signData);

        // Step 3: Sort strings in natural order
        sort($paramStrings, SORT_NATURAL);

        // Step 4: Join with semicolons
        $signString = implode(';', $paramStrings);

        // Step 5: Calculate HMAC-SHA512
        $hmac = hash_hmac('sha512', $signString, Booking::SECRET_KEY, true);

        // Step 6: Base64 encode
        return base64_encode($hmac);
    }

}

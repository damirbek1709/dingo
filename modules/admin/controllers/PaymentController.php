<?php

namespace app\modules\admin\controllers;


use app\models\Booking;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\admin\controllers\actions\FlashPayPayoutAction;

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
                "payment_id" => "testpaymentqwerty-78978798",
                "signature" => "",
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

}

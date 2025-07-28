<?php

namespace app\modules\admin\controllers;


use Yii;
use yii\web\Controller;
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

        // Set request data for testing
        Yii::$app->request->setBodyParams($testData);

        // Call the payout action
        return $this->runAction('flashpay-payout');
    }
}

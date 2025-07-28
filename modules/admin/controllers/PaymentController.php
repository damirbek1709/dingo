<?php

namespace app\modules\admin\controllers;

use app\models\Booking;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class PaymentController extends Controller
{
    /**
     * FlashPay API Configuration
     */
    private $apiBaseUrl = 'https://api.flashpay.kg'; // Replace with actual API URL
    private $merchantId;
    private $apiKey;
    private $apiSecret;

    public function init()
    {
        parent::init();

        // Load API credentials from config or params
        $this->merchantId = Booking::MERCHANT_ID ?? '';
        $this->apiKey = 'YG92lqrqanpxgrKpdWP24O6hXo41Co';
        $this->apiSecret = Booking::SECRET_KEY ?? '';
    }

    /**
     * Behaviors for access control and verb filtering
     */
    // public function behaviors()
    // {
    //     return [
    //         'access' => [
    //             'class' => AccessControl::class,
    //             'rules' => [
    //                 [
    //                     'allow' => true,
    //                     'roles' => ['@'], // Only authenticated users
    //                 ],
    //             ],
    //         ],
    //         'verbs' => [
    //             'class' => VerbFilter::class,
    //             'actions' => [
    //                 'payout' => ['POST'],
    //             ],
    //         ],
    //     ];
    // }

    /**
     * Individual Payout Action
     * Creates a payout to an individual recipient
     */
    public function actionPayout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Get request data
            $request = Yii::$app->request;
            $postData = $request->post();

            $postData = [
                'amount' => 1,
                'currency' => 'KGS',
                'recipient_account' => '4169585343246905', // Visa card number
                'recipient_name' => 'DAMIRBEK SYDYKOV',
                'recipient_phone' => '+996551170990',
                'recipient_email' => 'damirbek@gmail.com',
                'bank_code' => null, // Optional for card transfers
                'description' => 'Выплата',
                'callback_url' => 'https://dev.dingo.kg/payment/payout-callback',
                'return_url' => null
            ];


            // Validate required fields
            $requiredFields = ['amount', 'currency', 'recipient_account', 'recipient_name'];
            foreach ($requiredFields as $field) {
                if (empty($postData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '{$field}' is required",
                        'code' => 'VALIDATION_ERROR'
                    ];
                }
            }

            // Generate unique payout ID
            $payoutId = $this->generatePayoutId();

            // Prepare payout data
            $payoutData = [
                'merchant_order_id' => $payoutId,
                'amount' => floatval($postData['amount']),
                'currency' => $postData['currency'],
                'description' => $postData['description'] ?? 'Individual payout',
                'recipient' => [
                    'account_number' => $postData['recipient_account'],
                    'account_name' => $postData['recipient_name'],
                    'bank_code' => $postData['bank_code'] ?? null,
                    'phone' => $postData['recipient_phone'] ?? null,
                    'email' => $postData['recipient_email'] ?? null,
                ],
                'callback_url' => $postData['callback_url'] ?? Yii::$app->urlManager->createAbsoluteUrl(['payment/payout-callback']),
                'return_url' => $postData['return_url'] ?? null,
            ];

            // Get authentication token
            $authToken = $this->getAuthToken();
            if (!$authToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with FlashPay API',
                    'code' => 'AUTH_ERROR'
                ];
            }

            // Make API request
            $response = $this->makePayoutRequest($payoutId, $payoutData, $authToken);

            if ($response['success']) {
                // Log successful payout request
                Yii::info("Payout request created successfully: {$payoutId}", __METHOD__);

                return [
                    'success' => true,
                    'message' => 'Payout request created successfully',
                    'data' => [
                        'payout_id' => $payoutId,
                        'status' => $response['data']['status'] ?? 'pending',
                        'amount' => $payoutData['amount'],
                        'currency' => $payoutData['currency'],
                        'recipient' => $payoutData['recipient']['account_name'],
                    ]
                ];
            } else {
                return $response;
            }

        } catch (\Exception $e) {
            Yii::error("Payout request failed: " . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'message' => 'Internal server error occurred',
                'code' => 'INTERNAL_ERROR'
            ];
        }
    }

    /**
     * Get authentication token from FlashPay API
     */
    private function getAuthToken()
    {
        try {
            $url = $this->apiBaseUrl . '/auth-tokens';

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];

            $authData = [
                'merchant_id' => $this->merchantId,
                'api_key' => $this->apiKey,
            ];

            // Add signature if required
            $authData['signature'] = $this->generateSignature($authData);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($authData),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Yii::error("cURL error in auth request: {$error}", __METHOD__);
                return false;
            }

            if ($httpCode !== 200) {
                Yii::error("Auth request failed with HTTP code: {$httpCode}", __METHOD__);
                return false;
            }

            $responseData = json_decode($response, true);
            return $responseData['access_token'] ?? false;

        } catch (\Exception $e) {
            Yii::error("Auth token request exception: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Make payout request to FlashPay API
     */
    private function makePayoutRequest($payoutId, $payoutData, $authToken)
    {
        try {
            $url = $this->apiBaseUrl . "/v2/payment/individual-payout/{$payoutId}";

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $authToken,
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payoutData),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Yii::error("cURL error in payout request: {$error}", __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Network error occurred',
                    'code' => 'NETWORK_ERROR'
                ];
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200 || $httpCode === 201) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                $errorMessage = $responseData['message'] ?? 'Payout request failed';
                Yii::error("Payout request failed with HTTP code {$httpCode}: {$errorMessage}", __METHOD__);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'code' => $responseData['code'] ?? 'API_ERROR'
                ];
            }

        } catch (\Exception $e) {
            Yii::error("Payout request exception: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Request processing failed',
                'code' => 'PROCESSING_ERROR'
            ];
        }
    }

    /**
     * Generate unique payout ID
     */
    private function generatePayoutId()
    {
        return 'PO_' . strtoupper(uniqid()) . '_' . time();
    }

    /**
     * Generate signature for API authentication
     */
    private function generateSignature($data)
    {
        // Sort data by keys
        ksort($data);

        // Create query string
        $queryString = http_build_query($data);

        // Generate HMAC signature
        return hash_hmac('sha256', $queryString, $this->apiSecret);
    }

    /**
     * Callback action for payout status updates
     */
    public function actionPayoutCallback()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $request = Yii::$app->request;
            $postData = $request->post();

            // Validate callback signature
            if (!$this->validateCallbackSignature($postData)) {
                Yii::warning("Invalid callback signature received", __METHOD__);
                return ['status' => 'error', 'message' => 'Invalid signature'];
            }

            // Process callback data
            $payoutId = $postData['merchant_order_id'] ?? null;
            $status = $postData['status'] ?? null;

            if ($payoutId && $status) {
                // Update payout status in your database
                // Example: PayoutModel::updateStatus($payoutId, $status);

                Yii::info("Payout status updated: {$payoutId} -> {$status}", __METHOD__);

                return ['status' => 'success', 'message' => 'Callback processed'];
            }

            return ['status' => 'error', 'message' => 'Invalid callback data'];

        } catch (\Exception $e) {
            Yii::error("Callback processing failed: " . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'Processing failed'];
        }
    }

    /**
     * Validate callback signature
     */
    private function validateCallbackSignature($data)
    {
        $signature = $data['signature'] ?? '';
        unset($data['signature']);

        $expectedSignature = $this->generateSignature($data);
        return hash_equals($expectedSignature, $signature);
    }
}
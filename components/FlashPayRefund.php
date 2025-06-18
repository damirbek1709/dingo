<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;

class FlashPayRefund extends Controller
{
    /**
     * Flash Pay API configuration
     */
    private const FLASH_PAY_API_URL = 'https://gateway.flashpay.kg/v2/payment/card/refund';

    /**
     * Your Flash Pay project ID and secret key (get these from Flash Pay dashboard)
     */
    private $projectId;
    private $secretKey;

    public function __construct($id, $controller, $config = [])
    {
        parent::__construct($id, $controller, $config);

        // Set your Flash Pay credentials here or load from config
        $this->projectId = Yii::$app->params['flashpay']['project_id'] ?? null;
        $this->secretKey = Yii::$app->params['flashpay']['secret_key'] ?? null;

        if (!$this->projectId || !$this->secretKey) {
            throw new \Exception('Flash Pay credentials not configured');
        }
    }

    /**
     * Refund action
     * @param string $paymentId - Original payment ID to refund
     * @param int|null $amount - Amount to refund in minor currency units (cents). If null, full refund
     * @param string $currency - Currency code (e.g., 'USD', 'EUR')
     * @param string $description - Refund description
     * @param string|null $merchantRefundId - Unique refund ID in your system
     * @return array
     */
    public function actionRefund($paymentId, $amount = null, $currency = 'USD', $description = 'Refund', $merchantRefundId = null)
    {
        try {
            // Prepare request data
            $requestData = $this->prepareRefundData($paymentId, $amount, $currency, $description, $merchantRefundId);

            // Generate signature
            $signature = $this->generateSignature($requestData);
            $requestData['general']['signature'] = $signature;

            // Send request to Flash Pay
            $response = $this->sendRefundRequest($requestData);

            return [
                'success' => true,
                'data' => $response,
                'request_data' => $requestData // For debugging
            ];

        } catch (\Exception $e) {
            Yii::error('Flash Pay refund error: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare refund request data
     */
    private function prepareRefundData($paymentId, $amount, $currency, $description, $merchantRefundId)
    {
        $data = [
            'general' => [
                'project_id' => (int)$this->projectId,
                'payment_id' => (string)$paymentId,
                // signature will be added later
            ],
            'payment' => [
                'currency' => (string)$currency,
                'description' => (string)$description,
            ]
        ];

        // Add amount if specified (for partial refund)
        if ($amount !== null) {
            $data['payment']['amount'] = (int)$amount;
        }

        // Add merchant refund ID if specified
        if ($merchantRefundId !== null) {
            $data['payment']['merchant_refund_id'] = (string)$merchantRefundId;
        }

        return $data;
    }

    /**
     * Generate signature according to Flash Pay documentation
     * Critical: This must follow the exact algorithm from the docs
     */
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
        $hmac = hash_hmac('sha512', $signString, $this->secretKey, true);

        // Step 6: Base64 encode
        return base64_encode($hmac);
    }

    /**
     * Convert data array to parameter strings with full path notation
     * Format: parent:child:value
     */
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

    /**
     * Format value according to Flash Pay rules
     */
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

        return (string)$value;
    }

    /**
     * Send refund request to Flash Pay API
     */
    private function sendRefundRequest($data)
    {
        $jsonData = Json::encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::FLASH_PAY_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('CURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP Error: ' . $httpCode . ' Response: ' . $response);
        }

        $responseData = Json::decode($response);

        if (!$responseData) {
            throw new \Exception('Invalid JSON response: ' . $response);
        }

        return $responseData;
    }

    /**
     * Verify signature of Flash Pay callback/response (optional but recommended)
     */
    public function verifySignature($data, $receivedSignature)
    {
        // Remove signature from data
        $dataToVerify = $data;
        if (isset($dataToVerify['signature'])) {
            unset($dataToVerify['signature']);
        }

        // Generate signature for verification
        $expectedSignature = $this->generateSignature(['data' => $dataToVerify])['data'];

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Example usage method
     */
    public function actionExample()
    {
        // Example: Full refund
        $result1 = $this->actionRefund(
            paymentId: 'payment_123456',
            description: 'Customer requested refund'
        );

        // Example: Partial refund of $10.50 (1050 cents)
        $result2 = $this->actionRefund(
            paymentId: 'payment_123456',
            amount: 1050,
            currency: 'USD',
            description: 'Partial refund - damaged item',
            merchantRefundId: 'refund_' . time()
        );

        return $this->asJson([
            'full_refund' => $result1,
            'partial_refund' => $result2
        ]);
    }
}

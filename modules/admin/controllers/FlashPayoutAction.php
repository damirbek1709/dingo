<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\httpclient\Client;

/**
 * FlashPay Individual Payout Action
 *
 * This action handles P2P card payout requests to FlashPay.kg gateway
 * API Documentation: https://api-developers.flashpay.kg/api.html#/9k9cnm848bn6k-v2-payment-individual-payout
 */
class FlashPayPayoutAction extends Action
{
    /** @var string FlashPay Gateway API endpoint */
    public $apiUrl = 'https://gateway.flashpay.kg/v2/payment/individual/payout';

    /** @var int Project ID from FlashPay */
    public $projectId;

    /** @var string Secret key for signature generation */
    public $secretKey;

    /** @var int Request timeout in seconds */
    public $timeout = 30;

    /**
     * Initialize the action
     */
    public function init()
    {
        parent::init();

        if (empty($this->projectId)) {
            throw new \InvalidArgumentException('Project ID is required');
        }

        if (empty($this->secretKey)) {
            throw new \InvalidArgumentException('Secret key is required');
        }
    }

    /**
     * Execute the payout action
     *
     * @return array Response data
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Get request data
            $requestData = $this->getRequestData();

            // Validate required fields
            $this->validateRequest($requestData);

            // Prepare payload
            $payload = $this->preparePayload($requestData);

            // Generate signature
            $payload['general']['signature'] = $this->generateSignature($payload);

            // Make API request
            $response = $this->makeApiRequest($payload);

            // Process response
            return $this->processResponse($response);

        } catch (BadRequestHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Yii::error('FlashPay payout error: ' . $e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException('Internal server error occurred');
        }
    }

    /**
     * Get request data from POST body
     *
     * @return array
     * @throws BadRequestHttpException
     */
    protected function getRequestData()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new BadRequestHttpException('Only POST method is allowed');
        }

        $data = $request->getBodyParams();

        if (empty($data)) {
            $data = json_decode($request->getRawBody(), true);
        }

        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid request data format');
        }

        return $data;
    }

    /**
     * Validate required request fields
     *
     * @param array $data
     * @throws BadRequestHttpException
     */
    protected function validateRequest($data)
    {
        $requiredFields = [
            'payment_id' => 'Payment ID is required',
            'card.pan' => 'Card number is required',
            'customer.id' => 'Customer ID is required',
            'payment.amount' => 'Payment amount is required',
            'payment.currency' => 'Payment currency is required'
        ];

        foreach ($requiredFields as $field => $message) {
            if (!$this->getNestedValue($data, $field)) {
                throw new BadRequestHttpException($message);
            }
        }

        // Validate payment amount
        $amount = $this->getNestedValue($data, 'payment.amount');
        if (!is_numeric($amount) || $amount < 1) {
            throw new BadRequestHttpException('Payment amount must be a positive integer');
        }

        // Validate currency format (ISO 4217 alpha-3)
        $currency = $this->getNestedValue($data, 'payment.currency');
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new BadRequestHttpException('Currency must be in ISO 4217 alpha-3 format');
        }

        // Validate card number format
        $cardNumber = $this->getNestedValue($data, 'card.pan');
        if (!preg_match('/^\d{13,19}$/', str_replace([' ', '-'], '', $cardNumber))) {
            throw new BadRequestHttpException('Invalid card number format');
        }
    }

    /**
     * Prepare API payload
     *
     * @param array $data
     * @return array
     */
    protected function preparePayload($data)
    {
        $payload = [
            'general' => [
                'project_id' => $this->projectId,
                'payment_id' => $data['payment_id'],
            ],
            'card' => [
                'pan' => str_replace([' ', '-'], '', $data['card']['pan']),
            ],
            'customer' => [
                'id' => $data['customer']['id'],
            ],
            'payment' => [
                'amount' => (int) $data['payment']['amount'],
                'currency' => strtoupper($data['payment']['currency']),
            ]
        ];

        // Add optional general fields
        $optionalGeneralFields = [
            'terminal_callback_url',
            'referrer_url',
            'merchant_callback_url'
        ];

        foreach ($optionalGeneralFields as $field) {
            if (isset($data[$field])) {
                $payload['general'][$field] = $data[$field];
            }
        }

        // Add optional card fields
        $optionalCardFields = [
            'year',
            'month',
            'issue_year',
            'issue_month',
            'card_holder',
            'cvv',
            'save',
            'stored_card_type'
        ];

        foreach ($optionalCardFields as $field) {
            if (isset($data['card'][$field])) {
                $payload['card'][$field] = $data['card'][$field];
            }
        }

        // Add optional customer fields
        $optionalCustomerFields = [
            'identification_level',
            'country',
            'city',
            'state',
            'phone',
            'iin',
            'home_phone',
            'work_phone',
            'save',
            'account_save',
            'day_of_birth',
            'person_type',
            'birthplace',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'browser',
            'ip_address',
            'device_type',
            'device_id',
            'datetime',
            'screen_res',
            'session_id',
            'language',
            'zip',
            'address',
            'district',
            'street',
            'building',
            'account_id',
            'gender',
            'qq_account_number',
            'ssn',
            'device_fingerprint'
        ];

        foreach ($optionalCustomerFields as $field) {
            if (isset($data['customer'][$field])) {
                $payload['customer'][$field] = $data['customer'][$field];
            }
        }

        // Add optional payment fields
        $optionalPaymentFields = [
            'customer_amount',
            'customer_currency',
            'description',
            'end_to_end_id',
            'extra_param',
            'best_before',
            'challenge_indicator',
            'challenge_window',
            'reorder',
            'preorder_purchase',
            'preorder_date',
            'local_conversion_currency',
            'device_channel',
            'is_fast'
        ];

        foreach ($optionalPaymentFields as $field) {
            if (isset($data['payment'][$field])) {
                $payload['payment'][$field] = $data['payment'][$field];
            }
        }

        // Add other optional objects
        $optionalObjects = [
            'identify',
            'billing',
            'account',
            'shipping',
            'mpi_result',
            'sender',
            'avs_data',
            'gift_card',
            'details',
            'return_url',
            'callback'
        ];

        foreach ($optionalObjects as $object) {
            if (isset($data[$object]) && is_array($data[$object])) {
                $payload[$object] = $data[$object];
            }
        }

        return $payload;
    }

    /**
     * Generate signature for the request
     *
     * @param array $payload
     * @return string
     */
    protected function generateSignature($payload)
    {
        // Create signature string by concatenating specific fields
        // The exact signature generation logic should be implemented according to FlashPay documentation
        // This is a basic implementation - refer to FlashPay documentation for exact requirements

        $signatureData = [
            'project_id' => $payload['general']['project_id'],
            'payment_id' => $payload['general']['payment_id'],
            'amount' => $payload['payment']['amount'],
            'currency' => $payload['payment']['currency'],
        ];

        ksort($signatureData);
        $signatureString = implode(':', $signatureData) . ':' . $this->secretKey;

        return hash('sha256', $signatureString);
    }

    /**
     * Make API request to FlashPay
     *
     * @param array $payload
     * @return array
     * @throws ServerErrorHttpException
     */
    protected function makeApiRequest($payload)
    {
        $client = new Client([
            'timeout' => $this->timeout,
        ]);

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->apiUrl)
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->setContent(json_encode($payload))
            ->send();

        if (!$response->isOk) {
            Yii::error('FlashPay API error: ' . $response->content, __METHOD__);
            throw new ServerErrorHttpException('Payment gateway error: ' . $response->statusCode);
        }

        $responseData = json_decode($response->content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ServerErrorHttpException('Invalid response format from payment gateway');
        }

        return $responseData;
    }

    /**
     * Process API response
     *
     * @param array $response
     * @return array
     */
    protected function processResponse($response)
    {
        // Log successful transaction
        Yii::info('FlashPay payout successful: ' . json_encode($response), __METHOD__);

        return [
            'success' => true,
            'status' => $response['status'] ?? 'unknown',
            'request_id' => $response['request_id'] ?? null,
            'project_id' => $response['project_id'] ?? null,
            'payment_id' => $response['payment_id'] ?? null,
            'response' => $response
        ];
    }

    /**
     * Get nested array value using dot notation
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    protected function getNestedValue($array, $key)
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

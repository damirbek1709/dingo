<?php

namespace app\modules\admin\controllers;
use Yii;
use app\models\Booking;
use app\models\BookingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;

/**
 * BookingController implements the CRUD actions for Booking model.
 */
class BookingController extends Controller
{
    public $layout = "main";
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Booking models.
     *
     * @return string
     */
    public function actionIndex($object_id, $status = null, $guest_name = "")
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);

        $searchModel = new BookingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $current_date = date('Y-m-d');
        $active = "all_active";

        if ($guest_name) {
            $dataProvider->query->andFilterWhere(['LIKE', 'guest_name', $guest_name]);
        }

        $room_id = Yii::$app->request->get('room_id') ? Yii::$app->request->get('room_id') : null;
        if ($room_id) {
            $dataProvider->query->andFilterWhere(['room_id' => $room_id]);
        }

        $tariff_id = Yii::$app->request->get('tariff_id') ? Yii::$app->request->get('tariff_id') : null;
        if ($room_id) {
            $dataProvider->query->andFilterWhere(['tariff_id' => $tariff_id]);
        }

        $date_from = Yii::$app->request->get('date_from') ? Yii::$app->request->get('date_from') : null;
        if ($date_from) {
            $dataProvider->query->andFilterWhere(['>=', 'date_from', $date_from]);
        }

        $date_to = Yii::$app->request->get('date_to') ? Yii::$app->request->get('date_to') : null;
        if ($date_to) {
            $dataProvider->query->andFilterWhere(['<', 'date_to', $date_to]);
        }

        $date_book = Yii::$app->request->get('book_date') ? Yii::$app->request->get('book_date') : null;
        if ($date_book) {
            $dataProvider->query->andFilterWhere(['created_at' => $date_book]);
        }

        $status_arr = Yii::$app->request->get('status', []);
        if ($status_arr) {
            $dataProvider->query->andFilterWhere(['status' => $status_arr]);
        }
        if ($status_arr) {
            switch ($status) {
                case "future":
                    $dataProvider->query->andFilterWhere(['>=', 'date_from', $current_date]);
                    $active = "future_active";
                    break;
                case "past":
                    $dataProvider->query->andFilterWhere(['<', 'date_to', $current_date]);
                    $active = "past_active";
                    break;
                case "canceled":
                    $dataProvider->query->andFilterWhere(['status' => Booking::PAID_STATUS_CANCELED]);
                    $active = "cancel_active";
                    break;
                default:
                    $dataProvider->query->andFilterWhere(['object_id' => $object_id]);

            }
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'object_id' => $object_id,
            'active' => $active,
            "guest_name" => $guest_name,
            'room_id' => $room_id,
            'tariff_id' => $tariff_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'date_book' => $date_book,
            'object' => $object
        ]);
    }

    /**
     * Displays a single Booking model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionGetTariffs()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $room_id = Yii::$app->request->get('room_id');
        $object_id = Yii::$app->request->get('object_id');

        $tariffs = Booking::tariffList($object_id, $room_id);

        return $tariffs;
    }

    /**
     * Creates a new Booking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Booking();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Booking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Booking model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Booking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Booking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Booking::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionRefund($id)
    {
        try {
            $this->response->format = Response::FORMAT_JSON;
            $model = Booking::findOne($id);
            // Prepare request data
            $requestData = $this->prepareRefundData($model->transaction_number, $model->sum, 'KGS', $model->special_comment, Booking::MERCHANT_ID);

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

    public function actionCheckStatus($requestId)
    {
        // Set response format to JSON
        $this->response->format = Response::FORMAT_JSON;

        try {
            // Prepare status request data
            $requestData = [
                'project_id' => (int) Booking::MERCHANT_ID,
                'request_id' => (string) $requestId,
            ];

            // Generate signature
            $signature = $this->generateSignature($requestData);
            $requestData['signature'] = $signature;

            // Send status request to Flash Pay
            $response = $this->sendStatusRequest($requestData);

            return [
                'success' => true,
                'data' => $response,
                'request_data' => $requestData // For debugging
            ];

        } catch (\Exception $e) {
            Yii::error('Flash Pay status check error: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function sendStatusRequest($data)
    {
        $jsonData = Json::encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://gateway.flashpay.kg/v2/payment/status/request',
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
     * Prepare refund request data
     */
    private function prepareRefundData($paymentId, $amount, $currency, $description, $merchantRefundId)
    {
        $data = [
            'general' => [
                'project_id' => Booking::MERCHANT_ID,
                'payment_id' => (string) $paymentId,
                // signature will be added later
            ],
            'payment' => [
                'currency' => (string) $currency,
                'description' => (string) $description,
            ]
        ];

        // Add amount if specified (for partial refund)
        if ($amount !== null) {
            $data['payment']['amount'] = (int) $amount;
        }

        // Add merchant refund ID if specified
        if ($merchantRefundId !== null) {
            $data['payment']['merchant_refund_id'] = (string) $merchantRefundId;
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
        $hmac = hash_hmac('sha512', $signString, Booking::SECRET_KEY, true);

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

        return (string) $value;
    }

    /**
     * Send refund request to Flash Pay API
     */
    private function sendRefundRequest($data)
    {
        $jsonData = Json::encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://gateway.flashpay.kg/v2/payment/card/refund',
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




    public function actionRefund2($id)
    {
        $url = "https://gateway.flashpay.kg/v2/payment/card/refund";
        $model = Booking::findOne($id);

        $data = [
            "general" => [
                "project_id" => Booking::MERCHANT_ID,
                "payment_id" => $model->transaction_number, // Required: actual payment ID
                "signature" => $this->generateSignature($id),
                "terminal_callback_url" => "https://dev.digno.kg/booking/terminal-callback",
                "referrer_url" => "https://dev.digno.kg",
                "merchant_callback_url" => "https://dev.digno.kg//booking/merchant-callback",
            ],
            "merchant" => [
                "descriptor" => "Refund Descriptor",
                "data" => "Custom merchant data",
            ],
            "cash_voucher_data" => [
                "email" => $model->guest_email,
                "inn" => "123456789012",
                "group" => "group_id",
                "taxation_system" => 0,
                "payment_address" => "Your shop address",
                "positions" => [
                    [
                        "quantity" => 1,
                        "price" => $model->sum, // in tyiyn
                        "position_description" => $model->bookingRoomTitle(),
                        "tax" => 1,
                        "payment_method_type" => 1,
                        "payment_subject_type" => 1,
                        "nomenclature_code" => "ABC123"
                    ]
                ],
                "payments" => [
                    [
                        "payment_type" => 1,
                        "amount" => 1000
                    ]
                ],
                "order_id" => $model->id,
                "send_cash_voucher" => true
            ],
            "payment" => [
                "amount" => $model->sum,
                "currency" => $model->currency,
                "description" => "Customer refund",
                "merchant_refund_id" => $model->user_id
            ],
            "interface_type" => 0,
            "receipt_data" => [
                "positions" => [
                    [
                        "quantity" => 1,
                        "amount" => $model->sum,
                        "tax" => 1,
                        "tax_amount" => 0,
                        "description" => "Refunded Item"
                    ]
                ],
                "total_tax_amount" => 0,
                "common_tax" => 0
            ],
            "callback" => [
                "delay" => 0,
                "force_disable" => true
            ],
            "addendum" => [
                "lodging" => [
                    "check_out_date" => "2025-05-19",
                    "room" => [
                        "rate" => 999999999999,
                        "number_of_nights" => 1
                    ],
                    "total_tax" => 0,
                    "charges" => [
                        "room_service" => 0,
                        "bar_or_lounge" => 0,
                        // ... other charges
                        "health_club" => 0
                    ]
                ]
            ],
            "booking_info" => [
                "firstname" => "John",
                "surname" => "Doe",
                "email" => "john@example.com",
                "start_date" => "2025-05-10",
                "end_date" => "2025-05-12",
                "description" => "Hotel booking refund",
                "total" => 1000,
                "pax" => 2,
                "reference" => "REF123",
                "id" => "BKID001"
            ]
        ];



        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return $this->asJson(['error' => curl_error($ch)]);
        }

        curl_close($ch);

        return $this->asJson([
            'status' => $httpCode,
            'response' => json_decode($response, true),
        ]);
    }
}

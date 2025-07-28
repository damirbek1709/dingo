<?php

namespace app\modules\admin\controllers;
use app\models\Tariff;
use app\models\Transaction;
use Yii;
use app\models\Booking;
use app\models\BookingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;
use app\models\user\User;
use DateTime;

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
        $model = Booking::findOne($id);

        $transactionRequestData = [
            'general' => [
                'project_id' => (int) Booking::MERCHANT_ID,
                'payment_id' => (string) $model->transaction_number,
            ],
            'destination' => 'merchant'
        ];

        $status = "";

        $transaction_signature = $this->generateSignature($transactionRequestData);
        $transactionRequestData['general']['signature'] = $transaction_signature;
        $transaction_response = $this->sendTransactionRequest($transactionRequestData);

        if ($transaction_response && array_key_exists('payment', $transaction_response)) {
            $status = $transaction_response['payment']['status'];
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
            'payment_status' => $status
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

    public function actionPayout($id)
    {
        $url = 'https://gateway.flashpay.kg/v2/payment/individual/payout';
        $booking = Booking::findOne($id);
        $user = User::findOne($booking->user_id);
        $transaction = new Transaction();
        $transaction->status = Transaction::STATUS_NEW;
        $transaction->customer_id = $booking->user_id;

        $comission = $booking->sum / 100 * $user->fee_percent;
        $transaction->sum = $booking->sum - $comission;
        $transaction->save();

        $requestData = $this->prepareRefundData(
            $transaction->id,
            $transaction->sum,
            'KGS',
            "Выплата хосту",
            Booking::MERCHANT_ID
        );

        // Generate signature
        $signature = $this->generateSignature($requestData);


        $data = [
            "general" => [
                "project_id" => Booking::MERCHANT_ID,
                "payment_id" => $transaction->id,
                "signature" => $signature, // You must generate this correctly
                "terminal_callback_url" => "https://partner.dingo.kg/terminal-callback",
                "referrer_url" => "https://partner.dingo.kg/referrer",
                "merchant_callback_url" => "https://partner.dingo.kg/merchant-callback"
            ],
            "card" => [
                "pan" => "4169585343246905",
                "year" => 2026,
                "month" => 7,
                "issue_year" => 2021,
                "issue_month" => 8,
                "card_holder" => "DAMIRBEK SYDYKOV",
                "cvv" => "617",
                "save" => true,
                "stored_card_type" => 0
            ],
            "sender" => [
                "first_name" => "John",
                "middle_name" => "Middle",
                "last_name" => "Doe",
                "day_of_birth" => "1990-01-01",
                "birthplace" => "Bishkek",
                "phone" => "+996700000000",
                "residence" => "KG",
                "citizenship" => "KG",
                "person_type" => "individual",
                "account_number" => "40817810000000000001",
                "payment_purpose" => "Personal payment",
                "source_of_income" => "Salary",
                "beneficiary_relationship" => "Self",
                "identify" => [
                    "doc_number" => "AB123456",
                    "doc_type" => "passport",
                    "doc_issue_date" => "2010-01-01",
                    "doc_issue_by" => "MVD"
                ],
                "billing" => [
                    "country" => "KG",
                    "city" => "Bishkek",
                    "state" => "Chuy",
                    "address" => "Some address",
                    "postal" => "720000"
                ],
                "pan" => "4111111111111111",
                "wallet_id" => "wallet001",
                "address" => "Some address",
                "zip" => "720000",
                "city" => "Bishkek",
                "country" => "KG",
                "state" => "Chuy"
            ],
            "avs_data" => [
                "avs_post_code" => "720000",
                "avs_street_address" => "Some street 123"
            ],
            "payment" => [
                "amount" => 1,
                "currency" => "KGS",
                "customer_amount" => 1,
                "customer_currency" => "KGS",
                "description" => "Payment description",
                "end_to_end_id" => "e2eid123",
                "extra_param" => "",
                "best_before" => "2025-12-31T23:59:59Z",
                "challenge_indicator" => "",
                "challenge_window" => "",
                "reorder" => "",
                "preorder_purchase" => "",
                "preorder_date" => "",
                "gift_card" => [
                    "amount" => 0,
                    "currency" => "KGS",
                    "count" => 0
                ],
                "local_conversion_currency" => "KGS",
                "details" => [
                    "document_number" => "INV-001",
                    "document_date" => "2025-07-28"
                ],
                "device_channel" => "browser",
                "is_fast" => true
            ],
            "return_url" => [
                "success" => "https://partner.dingo.kg/success",
                "decline" => "https://partner.dingo.kg/decline",
                "return" => "https://partner.dingo.kg/return"
            ],
            "callback" => [
                "delay" => 0,
                "force_disable" => true
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return $this->asJson([
                'success' => false,
                'error' => curl_error($ch)
            ]);
        }

        curl_close($ch);

        return $this->asJson([
            'success' => true,
            'status' => $httpcode,
            'response' => json_decode($response, true)
        ]);
    }




    public function actionRefund($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = Booking::findOne($id);
            if (!$model) {
                throw new \Exception('Бронирование не найдено');
            }

            $sum = $model->sum;
            $tariff = Tariff::findOne($model->tariff_id);
            if ($tariff) {
                $current_date = date('Y-m-d');
                $book_checkin_date = $model->date_from;

                $checkin = new DateTime($book_checkin_date);
                $current = new DateTime($current_date);
                $interval = $checkin->diff($current);
                $sub_days = (int) $interval->format('%r%a');
                if ($sub_days < $tariff->penalty_days) {
                    $comission = $model->sum * $tariff->penalty_sum;
                    $sum = $model->sum - $comission;
                }
            }


            // Prepare request data
            $requestData = $this->prepareRefundData(
                $model->transaction_number,
                $sum,
                'KGS',
                $model->special_comment,
                Booking::MERCHANT_ID
            );

            // Generate signature
            $signature = $this->generateSignature($requestData);
            $requestData['general']['signature'] = $signature;

            // Send request to Flash Pay
            $response = $this->sendRefundRequest($requestData);
            if ($response) {
                $status_data = $this->sendTransactionRequest($requestData);
                if ($status_data) {
                    if ($status_data['payment']['method'] == 'card') {
                        $model->payment_type = $status_data['account']['type'];
                    }
                    if ($status_data['payment']['status'] == 'partially refunded' || $status_data['payment']['status'] == 'refunded') {
                        $model->return_status = Booking::REFUND_STATUS_RETURNED;
                    }
                    $model->save(false);
                }
            }

            // You can check $response content here before deciding success
            return [
                'success' => true,
                'message' => 'Заявка на возврат средств оформлена',
                'response' => $response
            ];
        } catch (\Exception $e) {
            Yii::error('Flash Pay refund error: ' . $e->getMessage(), __METHOD__);

            return [
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),

            ];
        }
    }

    private function checkTransactionStatus($transaction_number)
    {
        $transactionRequestData = [
            'general' => [
                'project_id' => (int) Booking::MERCHANT_ID,
                'payment_id' => (string) $transaction_number,
            ],
            'destination' => 'merchant'
        ];

        $status = "";

        $transaction_signature = $this->generateSignature($transactionRequestData);
        $transactionRequestData['general']['signature'] = $transaction_signature;
        $jsonData = Json::encode($transactionRequestData);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://gateway.flashpay.kg/v2/payment/status',
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

        //$responseData = Json::decode($response);

        if ($response) {
            throw new \Exception('Invalid JSON response: ' . $response);
        }

        return $response;
    }

    public function actionFinance()
    {
        $query_word = Yii::$app->request->get('query_word') ?? "";
        $tariff = Yii::$app->request->get('tariff') ?? "";

        $client = Yii::$app->meili->connect();
        $searchModel = new BookingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $active = "all_active";
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search($query_word, [
            'limit' => 10000
        ]);

        $object_arr = [];
        $hits = $res->getHits();
        foreach ($hits as $item) {
            $object_arr[] = $item['id'];
        }

        if ($tariff) {
            if ($tariff == Tariff::NO_CANCELLATION) {
                $dataProvider->query->andFilterWhere(['cancellation_type' => Tariff::NO_CANCELLATION]);
            } else {
                $dataProvider->query->andFilterWhere(['!=', 'cancellation_type', Tariff::NO_CANCELLATION]);
            }
        }

        $dataProvider->query->andFilterWhere(['object_id' => $object_arr])
            ->orFilterWhere(['transaction_number' => $query_word]);

        if (Yii::$app->request->get('date_from')) {
            $date_from = date('Y-m-d', strtotime(Yii::$app->request->get('date_from')));
            $dataProvider->query->andFilterWhere(['>=', 'date_from', $date_from]);
            $date_from_string = Yii::$app->request->get('date_from');
        } else {
            $date_from = null;
            $date_from_string = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
        }

        if (Yii::$app->request->get('date_to')) {
            $date_to = date('Y-m-d', strtotime(Yii::$app->request->get('date_to')));
            $dataProvider->query->andFilterWhere(['<=', 'date_to', $date_to]);
            $date_to_string = Yii::$app->request->get('date_to');
        } else {
            $date_to = null;
            $date_to_string = date('Y-m-d');
        }

        $status_arr = Yii::$app->request->get('status', []);
        if ($status_arr) {
            $dataProvider->query->andFilterWhere(['return_status' => $status_arr]);
        } else {
            $dataProvider->query->andFilterWhere(['return_status' => null]);
        }
        return $this->render('finance', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'active' => $active,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'date_from_string' => $date_from_string,
            'date_to_string' => $date_to_string,
            'query_word' => $query_word
        ]);
    }


    private function sendTransactionRequest($data)
    {
        $jsonData = Json::encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://gateway.flashpay.kg/v2/payment/status',
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
                'project_id' => (int) Booking::MERCHANT_ID,
                'payment_id' => (string) $paymentId,
            ],
            'payment' => [
                'currency' => (string) $currency,
                'description' => (string) $description,
            ]
        ];
        if ($amount !== null) {
            $data['payment']['amount'] = (int) $amount;
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
}

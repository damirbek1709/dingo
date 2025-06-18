<?php

namespace app\modules\admin\controllers;
use Yii;
use app\models\Booking;
use app\models\BookingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        $url = "https://gateway.flashpay.kg/v2/payment/card/refund";
        $model = Booking::findOne($id);

        if (!$model) {
            return $this->asJson(['error' => 'Booking not found']);
        }

        if (empty($model->transaction_number)) {
            return $this->asJson(['error' => 'No transaction number found']);
        }

        $refundAmount = $model->sum;

        // Prepare the core data structure
        $data = [
            "general" => [
                "project_id" => (int) Booking::MERCHANT_ID,
                "payment_id" => $model->transaction_number,
                "terminal_callback_url" => "https://dev.digno.kg/api/booking/terminal-callback",
                "referrer_url" => "https://dev.digno.kg",
                "merchant_callback_url" => "https://dev.digno.kg/api/booking/refund-callback",
            ],
            "payment" => [
                "amount" => (int) $refundAmount,
                "currency" => $model->currency ?? 'KGS',
                "description" => "Booking refund for reservation #" . $model->id,
                "merchant_refund_id" => "REFUND_" . $model->id . "_" . time()
            ],
            "cash_voucher_data" => [
                "email" => $model->guest_email,
                "inn" => "123456789012",
                "taxation_system" => 0,
                "payment_address" => "Your business address",
                "positions" => [
                    [
                        "quantity" => 1,
                        "price" => (int) $refundAmount,
                        "position_description" => $model->bookingRoomTitle(),
                        "tax" => 1,
                        "payment_method_type" => 1,
                        "payment_subject_type" => 1,
                        "nomenclature_code" => "ROOM_" . $model->id
                    ]
                ],
                "payments" => [
                    [
                        "payment_type" => 1,
                        "amount" => (int) $refundAmount
                    ]
                ],
                "order_id" => $model->id,
                "send_cash_voucher" => true
            ],
            "interface_type" => 0,
            "receipt_data" => [
                "positions" => [
                    [
                        "quantity" => 1,
                        "amount" => (int) $refundAmount,
                        "tax" => 1,
                        "tax_amount" => 0,
                        "description" => "Refund: " . $model->bookingRoomTitle()
                    ]
                ],
                "total_tax_amount" => 0,
                "common_tax" => 0
            ],
            "callback" => [
                "delay" => 0,
                "force_disable" => false
            ]
        ];

        // Flatten the data for signature generation (FlashPay might expect flattened structure)
        $signatureData = $this->flattenArrayForSignature($data);

        // Generate signature
        $signatureGenerator = new FlashPaySignatureGenerator(self::FLASHPAY_SECRET_KEY);
        $signature = $signatureGenerator->generateSignature($signatureData);

        // Add signature to the request
        $data['general']['signature'] = $signature;

        // Log for debugging
        \Yii::info('FlashPay signature data: ' . json_encode($signatureData), 'flashpay');
        \Yii::info('Generated signature: ' . $signature, 'flashpay');

        // Make the API request
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: DignoKG/1.0'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            \Yii::error('FlashPay cURL error: ' . $error, 'flashpay');
            return $this->asJson(['error' => 'Connection error: ' . $error]);
        }

        curl_close($ch);
        $responseData = json_decode($response, true);

        // Log response
        \Yii::info('FlashPay refund response: ' . $response, 'flashpay');

        // Handle response
        if ($httpCode === 200 && isset($responseData['status'])) {
            if ($responseData['status'] === 'success' || $responseData['status'] === 'processing') {
                // Update booking status
                $model->refund_status = 'processing';
                $model->refund_request_date = date('Y-m-d H:i:s');
                $model->save();

                return $this->asJson([
                    'success' => true,
                    'message' => 'Refund request submitted successfully',
                    'data' => $responseData
                ]);
            }
        }

        return $this->asJson([
            'success' => false,
            'status' => $httpCode,
            'error' => $responseData['message'] ?? 'Refund request failed',
            'response' => $responseData,
        ]);
    }

    /**
     * Helper method to flatten nested arrays for signature generation
     * FlashPay might expect a flat structure for signature calculation
     */
    private function flattenArrayForSignature($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '_' . $key;

            if (is_array($value) && !$this->isAssociativeArray($value)) {
                // Handle indexed arrays (like positions, payments)
                foreach ($value as $index => $item) {
                    if (is_array($item)) {
                        $result = array_merge($result, $this->flattenArrayForSignature($item, $newKey . '_' . $index));
                    } else {
                        $result[$newKey . '_' . $index] = $item;
                    }
                }
            } elseif (is_array($value)) {
                // Handle associative arrays
                $result = array_merge($result, $this->flattenArrayForSignature($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    /**
     * Check if array is associative
     */
    private function isAssociativeArray($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }


    // Test function to verify the signature generation matches FlashPay example
    function testFlashPaySignature()
    {
        $testData = [
            "project_id" => 12345,
            "payment_id" => "X03936",
            "payment_amount" => 2035,
            "payment_currency" => "USD",
            "payment_description" => "Guyliner purchase",
            "customer_first_name" => "Jack",
            "customer_id" => "user007",
            "customer_last_name" => "Sparrow",
            "customer_phone" => "02081234567",
            "close_on_missclick" => true
        ];

        $signature = Booking::generateSignature($testData);

        echo "Generated signature: " . $signature . "\n";
        echo "Expected signature:  SyA3cx/dmFrwjRcpbnwEK9zaklWKR9buIfTctQob/EHUTutFLpI0zWpSDFEWEwbZt/04i83395RCdEhtUMw83A==\n";
        echo "Match: " . ($signature === 'SyA3cx/dmFrwjRcpbnwEK9zaklWKR9buIfTctQob/EHUTutFLpI0zWpSDFEWEwbZt/04i83395RCdEhtUMw83A==' ? 'YES' : 'NO') . "\n";
    }



    /**
     * Handle refund callback from FlashPay
     */
    public function actionRefundCallback()
    {
        $request = \Yii::$app->request;
        $data = json_decode($request->getRawBody(), true);

        Yii::info('FlashPay refund callback: ' . json_encode($data), 'flashpay');

        // Verify the callback signature here

        if (isset($data['payment_id']) && isset($data['status'])) {
            $booking = Booking::find()
                ->where(['transaction_number' => $data['payment_id']])
                ->one();

            if ($booking) {
                $booking->refund_status = $data['status'];
                $booking->refund_completion_date = date('Y-m-d H:i:s');
                $booking->save();
            }
        }

        return $this->asJson(['status' => 'ok']);
    }


}

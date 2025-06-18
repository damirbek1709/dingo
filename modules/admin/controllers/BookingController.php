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
        $model = Booking::findOne($id);
        if (!$model) {
            return $this->asJson(['error' => 'Booking not found']);
        }

        if (empty($model->transaction_number)) {
            return $this->asJson(['error' => 'No transaction number found']);
        }

        $projectId = Booking::MERCHANT_ID;
        $secretKey = Booking::SECRET_KEY; // Replace with real secret
        $amount = $model->sum;
        $currency = $model->currency ?? 'KGS';

        // Build payload (without signature)
        $payload = [
            "general" => [
                "project_id" => $projectId,
                "payment_id" => $model->transaction_number,
                "merchant_callback_url" => "https://dev.digno.kg/api/booking/refund-callback",
            ],
            "payment" => [
                "amount" => $amount,
                "currency" => $currency,
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
                        "price" => $amount,
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
                        "amount" => $amount
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
                        "amount" => $amount,
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

        // Generate signature and attach it
        $payload['general']['signature'] = $this->generateFlashPaySignature($payload, $secretKey);

        //print_r(json_encode($payload));die();

        // Logging request
        Yii::info('FlashPay refund request: ' . json_encode($payload), 'flashpay');

        // Send via cURL
        $ch = curl_init("https://gateway.flashpay.kg/v2/payment/card/refund");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: DignoKG/1.0'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Yii::error('FlashPay cURL error: ' . $error, 'flashpay');
            return $this->asJson(['error' => 'Connection error: ' . $error]);
        }

        curl_close($ch);
        Yii::info('FlashPay refund response: ' . $responseRaw, 'flashpay');

        $response = json_decode($responseRaw, true);

        if ($httpCode === 200 && isset($response['status']) && $response['status'] === 'success') {
            $model->refund_status = 'processing';
            $model->refund_request_date = date('Y-m-d H:i:s');
            $model->save(false);

            return $this->asJson([
                'success' => true,
                'message' => 'Refund request submitted',
                'refund_id' => $response['refund_id'] ?? null
            ]);
        } else {
            return $this->asJson([
                'success' => false,
                'status' => $httpCode,
                'error' => $response['message'] ?? 'Refund failed',
                'response' => $response
            ]);
        }
    }

    public function actionCheckStatus($transactionId)
    {
        $projectId = Booking::MERCHANT_ID;
        $secretKey = Booking::SECRET_KEY; // Replace with your FlashPay secret key

        // Generate signature
        $signature = $this->generateStatusSignature($projectId, $transactionId, $secretKey);

        $payload = [
            'project_id' => $projectId,
            'payment_id' => $transactionId,
            'signature' => $signature,
        ];

        Yii::info('FlashPay status check request: ' . json_encode($payload), 'flashpay');

        $ch = curl_init("https://gateway.flashpay.kg/v2/payment/status");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: DignoKG/1.0'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Yii::error('FlashPay status check cURL error: ' . $error, 'flashpay');
            return $this->asJson(['error' => 'Connection error: ' . $error]);
        }

        curl_close($ch);
        $response = json_decode($responseRaw, true);

        Yii::info('FlashPay status check response: ' . $responseRaw, 'flashpay');

        if ($httpCode === 200 && isset($response['status'])) {
            return $this->asJson([
                'success' => true,
                'status' => $response['status'],
                'data' => $response,
            ]);
        } else {
            return $this->asJson([
                'success' => false,
                'http_code' => $httpCode,
                'error' => $response['message'] ?? 'Unknown error',
                'response' => $response,
            ]);
        }
    }

    private function generateStatusSignature(string $projectId, string $paymentId, string $secretKey): string
    {
        $data = $projectId . $paymentId . $secretKey;
        return base64_encode(hash_hmac('sha512', $data, $secretKey, true));
    }





    private function generateFlashPaySignature(array $payload, string $secretKey): string
    {
        $lines = $this->flattenPayload($payload);
        sort($lines, SORT_NATURAL);
        $joined = implode(';', $lines);
        return base64_encode(hash_hmac('sha512', $joined, $secretKey, true));
    }

    private function flattenPayload(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? $key : $prefix . ':' . $key;
            if (is_array($value)) {
                if (array_values($value) === $value) {
                    foreach ($value as $i => $subValue) {
                        $result = array_merge($result, $this->flattenPayload([$i => $subValue], $path));
                    }
                } else {
                    $result = array_merge($result, $this->flattenPayload($value, $path));
                }
            } else {
                $result[] = $path . ':' . (is_bool($value) ? (int) $value : $value);
            }
        }
        return $result;
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

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
        // Fetch booking
        $model = Booking::findOne($id);
        if (!$model)
            return $this->asJson(['error' => 'Booking not found']);
        if (empty($model->transaction_number)) {
            return $this->asJson(['error' => 'No transaction number found']);
        }

        $secretKey = Booking::SECRET_KEY; // Replace this!
        $projectId = Booking::MERCHANT_ID;
        $amount = $model->sum;

        // 1. Prepare request payload WITHOUT signature
        $payload = [
            'general' => [
                'project_id' => $projectId,
                'payment_id' => $model->transaction_number,
                'terminal_callback_url' => 'https://dev.digno.kg/api/booking/terminal-callback',
                'referrer_url' => 'https://dev.digno.kg',
                'merchant_callback_url' => 'https://dev.digno.kg/api/booking/refund-callback',
            ],
            'payment' => [
                'amount' => $amount,
                'currency' => $model->currency ?? 'KGS',
                'description' => 'Booking refund #' . $model->id,
                'merchant_refund_id' => 'REFUND_' . $model->id . '_' . time(),
            ],
            // add any other fields (e.g. customer data, receipt_data) here...
        ];

        // 2. Generate signature and add it into general block
        $payload['general']['signature'] = $this->generateFlashPaySignature($payload, $secretKey);
        echo $payload['general']['signature'];die();

        // Send to FlashPay
        Yii::info('Refund request: ' . json_encode($payload), 'flashpay');
        $response = Yii::$app->httpClient
            ->post('https://gateway.flashpay.kg/v2/payment/card/refund', json_encode($payload))
            ->setHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'DignoKG/1.0',
            ])
            ->send();

        Yii::info('Refund response: ' . $response->body, 'flashpay');

        $data = $response->data;
        if ($response->isOk && @$data['status'] === 'success') {
            $model->refund_status = 'processing';
            $model->refund_request_date = date('Y-m-d H:i:s');
            $model->save(false);
            return $this->asJson([
                'success' => true,
                'refund_id' => $data['refund_id'] ?? null,
            ]);
        }

        return $this->asJson(['success' => false, 'response' => $data]);
    }

    /**
     * Build signature per FlashPay Gate API specification:
     * Flatten, sort, join with semicolons, HMAC-SHA512 + Base64.
     *
     * @param array  $payload   Request data WITHOUT signature field
     * @param string $secretKey Your secret key
     * @return string           Base64-encoded signature
     */
    private function generateFlashPaySignature(array $payload, string $secretKey): string
    {
        $lines = $this->flattenKeys($payload);
        sort($lines, SORT_NATURAL);
        $flat = implode(';', $lines);
        $hmac = hash_hmac('sha512', $flat, $secretKey, true);
        return base64_encode($hmac);
    }

    /**
     * Recursively flatten array into "parent:…:key:value" lines.
     * Includes array indices for each element.
     *
     * @param array  $data
     * @param string $prefix
     * @return array
     */
    private function flattenKeys(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $val) {
            $path = $prefix === '' ? $key : "{$prefix}:{$key}";
            if (is_array($val)) {
                // numeric arrays – include index
                if (array_values($val) === $val) {
                    foreach ($val as $i => $elem) {
                        $out = array_merge($out, $this->flattenKeys([$i => $elem], $path));
                    }
                } else {
                    $out = array_merge($out, $this->flattenKeys($val, $path));
                }
            } else {
                $value = $val === true ? '1' : ($val === false ? '0' : (string) $val);
                $out[] = "{$path}:{$value}";
            }
        }
        return $out;
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

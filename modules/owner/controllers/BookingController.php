<?php

namespace app\modules\owner\controllers;
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
        $searchModel = new BookingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['object_id' => $object_id]);
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
        if ($tariff_id) {
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
        if ($status) {
            switch ($status) {
                case "future":
                    $dataProvider->query->where(['>', 'date_from', $current_date]);
                    $active = "future_active";
                    break;
                case "past":
                    $dataProvider->query->where(['<', 'date_to', $current_date]);
                    $active = "past_active";
                    break;
                case "canceled":
                    $dataProvider->query->where(['status' => Booking::PAID_STATUS_CANCELED]);
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

        $data = [
            "general" => [
                "project_id" => Booking::MERCHANT_ID,
                "payment_id" => $model->transaction_number, // Required: actual payment ID
                "signature" => "your_signature", // Required: calculated as per API spec
                "terminal_callback_url" => "https://partner.digno.kg/api/booking/terminal-callback",
                "referrer_url" => "https://partner.digno.kg",
                "merchant_callback_url" => "https://yourdomain.com/merchant-callback",
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

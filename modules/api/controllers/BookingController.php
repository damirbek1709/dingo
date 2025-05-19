<?php

namespace app\modules\api\controllers;

use app\models\BookingSearch;
use app\models\FlashPay;
use app\models\Booking;
use app\models\Comfort;
use app\models\RoomComfort;
use app\models\user\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use DateTime;
use DateInterval;
use yii\data\Pagination;
use app\models\Objects;
use app\models\RoomCat;
use app\components\flashpay\Gate;

class BookingController extends BaseController
{
    public $modelClass = 'app\models\Booking';

    /**
     * @inheritDoc
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete'], $actions['options']);



        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'index',
            'add',
            'webhook',
        ];


        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['add', 'category-comfort-title', 'similar', 'room-comfort-title', 'exchange','list'],
                    'roles' => ['@', '?'],
                ],

                [
                    'allow' => true,
                    'actions' => ['list', 'view', 'list2', 'search', 'webhook'],
                    'roles' => ['@', '?', 'admin', 'owner'],
                ],
                [
                    'allow' => true,
                    'actions' => ['edit'],
                    'roles' => ['@'],
                    //'roles' => ['updatePost'],
                    // 'roleParams' => function () {
                    //     //return ['post' => Post::findOne(['id' => Yii::$app->request->get('id')])];
                    // },
                ]
            ]
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'add' => ['POST'],
                'webhook' => ['POST'],
                'list'=>['GET']
            ],
        ];

        return $behaviors;
    }

    /**
     * @return mixed
     */


    /**
     * List own posts.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionOwn()
    {
        $categories = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'category_id');
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true, null);
        //$dataProvider->query->andFilterWhere(['in', 'cat.id', $$categories]);
        // Get all models from the dataProvider
        $models = $dataProvider->getModels();

        // Unserialize the 'tags' field for each model
        foreach ($models as &$model) {
            if (isset($model->tags) && is_string($model->tags)) {
                $model->tags = unserialize($model->tags);
            }
        }

        // Update the models in the dataProvider
        $dataProvider->setModels($models);

        return $dataProvider;
    }



    

    public function actionAdd()
    {
        $response['success'] = false;
        $model = new Booking();
        $model->object_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'object_id');
        $model->room_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'room_id');
        $model->sum = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'sum');
        $model->tariff_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'tariff_id');
        $model->date_from = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'date_from');
        $model->date_to = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'date_to');
        $model->guest_email = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'guest_email');
        $model->guest_name = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'guest_name');
        $model->guest_phone = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'guest_phone');
        $model->other_guests = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'other_guests');
        $model->status = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'status');
        $model->user_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'user_id');
        $model->special_comment = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'special_comment');
        $model->currency = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'currency');
        $model->cancellation_type = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'cancellation_type');
        $model->cancellation_penalty_sum = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'cancellation_penalty_sum');
        $model->status = Booking::PAID_STATUS_NOT_PAID;
        $currency = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'currency');

        if ($model->save()) {
            $arr = [
                'sum' => $model->sum,
                'booking_id' => $model->id,
                'currency' => $currency,
                'user_id' => Yii::$app->user->id,
                'transaction_number' => (int) $model->id + 1000000
            ];
            $response['success'] = true;
            $response['message'] = 'Booking added successfully';
            $response['url'] = Booking::pay($arr);
            $response['transaction_number'] = $model->transaction_number;
        } else {
            $response['message'] = $model->errors;
        }

        return $response;
    }

    public function actionWebhook()
    {
        $rawPostData = Yii::$app->request->getRawBody();
        $jsonData = json_decode($rawPostData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Yii::error('Invalid JSON received: ' . $rawPostData, 'webhook');
            return $this->asJson(['status' => 'error', 'message' => 'Invalid JSON']);
        }

        $status = $jsonData['payment']['status'];
        if ($status == 'success') {
            $booking = Booking::find()->where(['transaction_number' => $jsonData['payment']['id']])->one();
            $booking->status = Booking::PAID_STATUS_PAID;
            $booking->save(false);
        }
        Yii::info('Webhook data received: ' . print_r($jsonData, true), 'webhook');

        return "OK";
    }

    public function actionList($finished = false, $future = false ,$canceled= false)
    {   

        $searchModel = new BookingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $current_date = date('Y-m-d');
        if($finished){
            $dataProvider->query->andFilterWhere(['>','date_from', $current_date]);
        }
        if($canceld){
            $dataProvider->query->andFilterWhere(['status'=>Booking::PAID_STATUS_CANCELED]);
        }
        if($future){
            $dataProvider->query->andFilterWhere(['<','date_to', $current_date]);
        }
        
        $dataProvider->query->andFilterWhere()
        return $dataProvider;
    }

}

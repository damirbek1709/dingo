<?php

namespace app\modules\api\controllers;

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
            'webhook'
        ];


        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['add', 'category-comfort-title', 'similar', 'room-comfort-title', 'exchange'],
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



    public function actionList()
    {
        $filters = [];
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $queryWord = Yii::$app->request->get('query_word', '');
        $fromDate = Yii::$app->request->get('from_date');
        $toDate = Yii::$app->request->get('to_date');
        $type = (int) Yii::$app->request->get('type', null);
        $amount = (int) Yii::$app->request->get('amount', null);
        $guestAmount = (int) Yii::$app->request->get('guest_amount', 1);

        $user_auth = null;
        $token = Yii::$app->request->headers->get('Authorization');
        if ($token && preg_match('/^Bearer\s+(.*?)$/', $token, $matches)) {
            $user_auth = $matches[1]; // Extract token
        }
        if ($type && $user_auth) {
            $hit = $index->search($queryWord, [
                'limit' => 1
            ])->getHits();

            $user = User::find()->where(['auth_key' => $user_auth])->one();
            $saved_data = $user->search_data ? unserialize($user->search_data) : [];
            if ($user->search_data === null) {
                if ($type == Objects::SEARCH_TYPE_REGION) {
                    $translit_word = isset($hit[0]['region']) ? $hit[0]['region'] : [];
                    $saved_data[] = [
                        'name' => $translit_word,
                        'region' => $queryWord,
                        'amount' => $amount
                    ];

                } elseif ($type == Objects::SEARCH_TYPE_HOTEL) {
                    $translit_word = isset($hit[0]['name']) ? $hit[0]['name'] : [];
                    $saved_data[] = [
                        'type' => $type,
                        'name' => $translit_word
                    ];
                } elseif ($type == Objects::SEARCH_TYPE_CITY) {
                    $translit_word = isset($hit[0]['city']) ? $hit[0]['city'] : [];
                    $saved_data[] = [
                        'type' => $type,
                        'name' => $translit_word,
                        'amount' => $amount
                    ];
                }

                $user->search_data = serialize($saved_data);
            } else {
                $saved_data = unserialize($user->search_data);
                if (count($saved_data) > 2) {
                    array_shift($saved_data);
                    if ($type == Objects::SEARCH_TYPE_REGION) {
                        $saved_data[] = [
                            'type' => $type,
                            'name' => $queryWord,
                            'amount' => $amount
                        ];
                    } elseif ($type == Objects::SEARCH_TYPE_HOTEL) {
                        $saved_data[] = [
                            'type' => $type,
                            'name' => $queryWord
                        ];
                    } elseif ($type == Objects::SEARCH_TYPE_CITY) {
                        $saved_data[] = [
                            'type' => $type,
                            'name' => $queryWord,
                            'amount' => $amount
                        ];
                    }
                } else {
                    if ($type == Objects::SEARCH_TYPE_REGION) {
                        $saved_data[] = [
                            'type' => $type,
                            'region' => $queryWord,
                            'amount' => $amount
                        ];
                    } elseif ($type == Objects::SEARCH_TYPE_HOTEL) {
                        $saved_data[] = [
                            'type' => $type,
                            'name' => $queryWord
                        ];
                    } elseif ($type == Objects::SEARCH_TYPE_CITY) {
                        $saved_data[] = [
                            'type' => $type,
                            'name' => $queryWord,
                            'amount' => $amount
                        ];
                    }
                }
                $user->search_data = serialize($saved_data);
            }
            $user->save(false);
        }

        // Base filter: guest amount
        $filters[] = 'rooms.guest_amount >= ' . $guestAmount;

        // Date availability filtering
        if ($fromDate && $toDate) {
            $period = new \DatePeriod(
                new \DateTime($fromDate),
                new \DateInterval('P1D'),
                (new \DateTime($toDate))
            );

            $searchDates = [];
            foreach ($period as $date) {
                $searchDates[] = $date->format('d-m-Y');
            }

            $filters[] = 'NOT rooms.not_available_dates IN [' .
                implode(',', array_map(function ($date) {
                    return '"' . $date . '"';
                }, $searchDates)) .
                ']';
        }

        $pageSize = 10;
        $page = (int) Yii::$app->request->get('page', 1);
        $offset = ($page - 1) * $pageSize;

        // Fetch extra results to sort locally
        $searchResults = $index->search($queryWord, [
            'filter' => $filters,
            'limit' => $pageSize * 5,
            'offset' => 0
        ]);

        $hits = $searchResults->getHits();

        // Calculate from_price
        foreach ($hits as &$hit) {
            $minPrice = PHP_FLOAT_MAX;
            if (!empty($hit['rooms'])) {
                foreach ($hit['rooms'] as $room) {
                    $priceIndex = $guestAmount - 1;
                    if (isset($room['tariff']) && is_array($room['tariff'])) {
                        foreach ($room['tariff'] as $tariff) {
                            if (isset($tariff['prices']) && is_array($tariff['prices'])) {
                                foreach ($tariff['prices'] as $priceData) {
                                    if (
                                        isset($priceData['price_arr']) &&
                                        is_array($priceData['price_arr']) &&
                                        isset($priceData['price_arr'][$priceIndex])
                                    ) {
                                        $currentPrice = $priceData['price_arr'][$priceIndex];
                                        if ($currentPrice < $minPrice) {
                                            $minPrice = $currentPrice;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $hit['from_price'] = $minPrice === PHP_FLOAT_MAX ? null : $minPrice;
        }

        // Sort by from_price ascending
        usort($hits, function ($a, $b) {
            return ($a['from_price'] ?? PHP_FLOAT_MAX) <=> ($b['from_price'] ?? PHP_FLOAT_MAX);
        });

        // Paginate after sorting
        $totalCount = count($hits);
        $paginatedHits = array_slice($hits, $offset, $pageSize);

        $arr = [
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'page' => $page,
            'data' => $paginatedHits,
        ];

        return $arr;
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
                'transaction_number' => (int) $model->id + 10000000000
            ];
            $response['success'] = true;
            $response['message'] = 'Booking added successfully';
            $response['url'] = Booking::pay($arr);
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

        $logFile = Yii::getAlias('@app/runtime/logs/webhook.log');
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . print_r($jsonData, true) . PHP_EOL, FILE_APPEND);

        $status = $jsonData['payment']['status'];
        if ($status == 'success') {
            $booking = Booking::find()->where(['transaction_number' => $jsonData['payment']['id']])->one();
            $booking->status = Booking::PAID_STATUS_PAID;
            $booking->save(false);
        }
        Yii::info('Webhook data received: ' . print_r($jsonData, true), 'webhook');

        return "OK";
    }

}

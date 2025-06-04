<?php

namespace app\modules\api\controllers;

use app\models\Comfort;
use app\models\Oblast;
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

class ObjectController extends BaseController
{
    public $modelClass = 'app\models\Object';

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
            'recommended-price',
            'view',
            'ratings',
            'save-search-guest',
            'cities',
            'rating-count-grades',
            'list',
            'list2',
            'category-comfort-title',
            'search',
            'room-images',
            'similar',
            'exchange',
            'search-stats'
        ];


        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['add', 'category-comfort-title', 'similar', 'room-comfort-title', 'exchange', 'search-stats'],
                    'roles' => ['@', '?'],
                ],

                [
                    'allow' => true,
                    'actions' => ['list', 'view', 'list2', 'search'],
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
                'edit' => ['POST'],
                'list' => ['GET'],
                'search' => ['GET'],
                'list2' => ['GET'],
                'remove' => ['POST'],
                'activate' => ['POST'],
                'deactivate' => ['POST'],
                'up' => ['POST'],
                'remove-images' => ['POST'],
                'remove-image' => ['POST'],
                'set-image-as-main' => ['POST'],
                'upnote' => ['POST'],
                'up-schedule-new' => ['POST'],
                'delete-search' => ['POST'],
                'add-to-favorites' => ['POST'],
                'similar' => ['GET'],
                'remove-from-favorites' => ['POST'],
                'category-comfort-title' => ['GET'],
                'room-comfort-title' => ['GET'],
                'room-images' => ['GET'],
                'exchange' => ['GET'],
                'search-stats' => ['GET']
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

    /**
     * List favorites posts.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionFavorites()
    {
        $identity = Yii::$app->user->identity;
        return $identity->favoritePosts;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionAddToFavorites()
    {
        $response["success"] = false;
        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $user_id = Yii::$app->user->id;
        $favorite = new Favorite();

        $favorite->user_id = $user_id;
        $favorite->post_id = $post_id;

        if ($favorite->save()) {
            $response["success"] = true;
            $response["message"] = 'Объявление добавлено в избранное.';
            $response["favorite"] = $favorite;
        } else {
            $response["errors"] = $favorite->errors;
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveFromFavorites()
    {
        $response["success"] = false;
        if (Yii::$app->request->post('post_id')) {
            $post_id = Yii::$app->request->post('post_id');
        } else {
            $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        }

        $favorite = $this->findFavorite($post_id);

        if ($favorite->delete()) {
            $response["success"] = true;
            $response["message"] = 'Объявление удалено из избранного.';
        } else {
            $response["errors"] = 'Не удалось удалить из избранного.';
        }

        return $response;
    }

    /**
     * Minimum-Maximum price.
     * @return mixed
     */
    public function actionRecommendedPrice()
    {
        $minimum_usd = null;
        $minimum_kgs = null;
        $maximum_usd = null;
        $maximum_kgs = null;

        $searchModel = new PostPriceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->sort->defaultOrder = [
            'price_usd' => SORT_ASC,
            'price_kgs' => SORT_ASC,
        ];

        $minimum = $dataProvider->query->one();

        $dataProvider->sort->defaultOrder = [
            'price_usd' => SORT_DESC,
            'price_kgs' => SORT_DESC,
        ];

        $maximum = $dataProvider->query->one();

        $result = [
            'minimum_usd' => $minimum !== null ? $minimum->price_usd : null,
            'minimum_kgs' => $minimum !== null ? $minimum->price_kgs : null,
            'maximum_usd' => $maximum !== null ? $maximum->price_usd : null,
            'maximum_kgs' => $maximum !== null ? $maximum->price_kgs : null,
        ];

        return $result;
    }


    public function actionRoomImages($id)
    {
        $model = RoomCat::findOne($id);
        return $model->getImages();
    }

    /**
     * Add a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAdd()
    {
        $response = ['result' => false];

        $user = Yii::$app->user->identity;
        if ($user) {
            $add_arr = [
                'id' => (int) $this->lastIncrement() + 1,
                'name' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'name'),
                'type' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'type'),
                'city' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'city'),
                'address' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'address'),
                'currency' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'currency'),
                'features' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'features'),
                'phone' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'phone'),
                'site' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'site'),
                'check_in' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'check_in'),
                'check_out' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'check_out'),
                'reception' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'reception'),
                'description' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'description'),
                'lat' => (float) ArrayHelper::getValue(Yii::$app->request->bodyParams, 'lat'),
                'lon' => (float) ArrayHelper::getValue(Yii::$app->request->bodyParams, 'lon'),
                'uploadImages' => UploadedFile::getInstancesByName('images'),
                'email' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email'),
                'user_id' => Yii::$app->user->id,
            ];

            $client = Yii::$app->meili->connect();
            if ($client->index('object')->addDocuments($add_arr)) {
                $response['result'] = true;
                $response['message'] = 'Object added successfully';
            }
        } else {
            $response['message'] = 'Unauthorized';
        }
        return $response;
    }


    public function actionEdit()
    {
        $response = ['result' => false];

        $user = Yii::$app->user->identity;
        $id = (int) ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');
        if ($user) {
            $add_arr = [
                'id' => $id,
                'name' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'name_ky'),
                'name_ky' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'name_en'),
                'name_en' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'name'),
                'type' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'type'),
                'city' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'city'),
                'city_id' => (int) ArrayHelper::getValue(Yii::$app->request->bodyParams, 'city_id'),
                'address' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'address'),
                'currency' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'currency'),
                'features' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'features'),
                'phone' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'phone'),
                'site' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'site'),
                'check_in' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'check_in'),
                'check_out' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'check_out'),
                'reception' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'reception'),
                'description' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'description'),
                'lat' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'lat'),
                'lon' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'lon'),
                'uploadImages' => UploadedFile::getInstancesByName('images'),
                'email' => ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email'),
                'user_id' => Yii::$app->user->id
            ];

            $client = Yii::$app->meili->connect();
            if ($client->index('object')->updateDocuments($add_arr)) {
                $response['result'] = true;
                $response['message'] = 'Object added successfully';
            }
        } else {
            $response['message'] = 'Unauthorized';
        }
        return $response;
    }

    public function actionList2()
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $searchResults = $index->search('', [
            // 'filter' => implode(' AND ', $filters),
            // 'sort' => [$priceField . ':asc'],
            'limit' => 100
        ]);
        return $searchResults->getHits();
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
        $status = Objects::STATUS_PUBLISHED;
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
                    $translit_word = isset($hit[0]['oblast_id']) ? $hit[0]['oblast_id'] : [];
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
        $filters[] = 'status = ' . $status;

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

    public function actionSearchStats()
    {
        $query = Yii::$app->request->get('query', '');
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        $oblast_query = Oblast::find()->all();
        $regions = [];
        foreach ($oblast_query as $item) {
            $regions[$item->id]['name'] = $item->title;
            $regions[$item->id]['name_en'] = $item->title_en;
            $regions[$item->id]['name_ky'] = $item->title_ky;
            $regions[$item->id]['amount'] = 0;

            $searchResponse = $index->search('', [
                'filter' => "oblast_id = \"Бишкек\"",
                //'limit' => 1,
            ]);
            $result[$item->id]['amount'] = $searchResponse->getEstimatedTotalHits();
        }

        $result = [
            "regions" => $regions
        ];

        return $result;
    }


    public function actionSearch()
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        $query = Yii::$app->request->get('query', '');

        $results = [
            'regions' => [],
            'hotels' => [],
            'oblast' => []
        ];

        if (!empty($query)) {
            $hotelMatches = $index->search($query, [
                'filter' => 'status = ' . Objects::STATUS_PUBLISHED,
                'limit' => 100 // Adjust as needed
            ])->getHits();

            
        
            $matchedHotelCount = 0;
            $matchedHotelName = [];
        
            foreach ($hotelMatches as $hit) {
                if (!empty($hit['name'])) {
                    $matchedHotelName = $hit['name'];
                    $matchedHotelCount++;
                    break; // Use the first exact matched hotel
                }
            }
        
            if ($matchedHotelCount > 0) {
                $results['hotels'][] = [
                    'name' => $matchedHotelName,
                    'amount' => $matchedHotelCount,
                    'type' => Objects::SEARCH_TYPE_HOTEL
                ];
            }
        }

        // Faceted count search
        $facetSearch = $index->search('', [
            'filter' => 'status = ' . Objects::STATUS_PUBLISHED,
            'facets' => ['city', 'name', 'oblast_id'],
            'limit' => 0
        ]);

        $cityCounts = $facetSearch->getFacetDistribution()['city'] ?? [];
        $oblastCounts = $facetSearch->getFacetDistribution()['oblast_id'] ?? [];

        

        $regionModels = Objects::regionList();
        foreach ($regionModels as $model) {
            $titles = [
                $model->title,
                $model->title_en,
                $model->title_ky
            ];

            $oblastAmount = 0;
            foreach ($titles as $variant) {
                foreach ($oblastCounts as $oblastName => $count) {
                    if (mb_strtolower($oblastName) === mb_strtolower($variant)) {
                        $oblastAmount += $count;
                    }
                }
            }

            $results['regions'][] = [
                'name' => $titles,
                'amount' => $oblastAmount,
                'type' => Objects::SEARCH_TYPE_REGION // create this constant if needed
            ];
        }

        // User search history
        $user_auth = null;
        $token = Yii::$app->request->headers->get('Authorization');
        $user_search_data = [];

        if ($token && preg_match('/^Bearer\s+(.*?)$/', $token, $matches)) {
            $user_auth = $matches[1];
        }

        if ($user_auth) {
            $user = User::find()->where(['auth_key' => $user_auth])->one();
            if ($user && $user->search_data) {
                $user_search_data = unserialize($user->search_data);
            }
        }

        $results['user_search_data'] = $user_search_data;

        return $results;
    }


    /**
     * Find all matches where the option starts with the search term (case insensitive)
     * 
     * @param string $searchTerm
     * @param array $availableOptions
     * @return array
     */
    private function getPrefixMatches($searchTerm, $availableOptions)
    {
        $matches = [];
        $searchTermLower = mb_strtolower($searchTerm);

        foreach ($availableOptions as $option) {
            if (mb_strtolower(mb_substr($option, 0, mb_strlen($searchTerm))) === $searchTermLower) {
                $matches[] = $option;
            }
        }
        return $matches;
    }

    /**
     * Fuzzy matching for 3+ letters (this part you already have)
     * 
     * @param string $searchTerm
     * @param array $availableOptions
     * @param float $defaultThreshold
     * @return array
     */
    private function findBestMatches($searchTerm, $availableOptions, $defaultThreshold = 70)
    {
        $matches = [];
        $searchTerm = strtolower(trim($searchTerm));

        if (empty($searchTerm)) {
            return $matches;
        }

        $threshold = $defaultThreshold;
        $length = mb_strlen($searchTerm);
        if ($length < 4) {
            $threshold = max(40, $defaultThreshold - (20 * (4 - $length)));
        }

        $exactPrefixMatch = ($length <= 2);

        foreach ($availableOptions as $option) {
            $optionLower = strtolower($option);

            if ($exactPrefixMatch && strpos($optionLower, $searchTerm) === 0) {
                $matches[] = $option;
                continue;
            }

            $percent = 0;
            similar_text($searchTerm, $optionLower, $percent);

            if ($percent >= $threshold) {
                $matches[] = $option;
            }
        }

        return $matches;
    }



    /**
     * Find best matches for a search term among available options
     * 
     * @param string $searchTerm The term to search for
     * @param array $availableOptions Array of available options to match against
     * @param float $threshold Minimum similarity percentage (default: 70)
     * @return array Array of matching options
     */


    public function actionCategoryComfortTitle()
    {
        $arr = [
            'ru' => [
                Comfort::COMFORT_CAT_SERVICE => Yii::t('app', 'Услуги'),
                Comfort::COMFORT_CAT_SPORT => Yii::t('app', 'Спорт и отдых'),
                Comfort::COMFORT_CAT_GENERAL => Yii::t('app', 'Общее'),
                Comfort::COMFORT_CAT_POOL => Yii::t('app', 'Бассейн и пляж'),
                Comfort::COMFORT_CAT_CHILDREN => Yii::t('app', 'Для детей'),
                Comfort::COMFORT_CAT_WORK => Yii::t('app', 'Для работы'),
                Comfort::COMFORT_CAT_AVAILABILITY => Yii::t('app', 'Доступность'),
                Comfort::COMFORT_CAT_WINTER_SPORTS => Yii::t('app', 'Зимние виды спорта'),
                Comfort::COMFORT_CAT_ANIMALS => Yii::t('app', 'Животные'),
                Comfort::COMFORT_CAT_INTERNET => Yii::t('app', 'Интернет'),
                Comfort::COMFORT_CAT_BEATY => Yii::t('app', 'Красота и здоровье'),
                Comfort::COMFORT_CAT_PARKING => Yii::t('app', 'Парковка'),
                Comfort::COMFORT_CAT_STAFF_SPEAKS => Yii::t('app', 'Персонал говорит'),
                Comfort::COMFORT_CAT_TRANSFER => Yii::t('app', 'Трансфер'),
                Comfort::COMFORT_CAT_SANITAR => Yii::t('app', 'Санитарные меры'),
            ],
            'kg' => [
                Comfort::COMFORT_CAT_SERVICE => Yii::t('app', 'Кызматтар'),
                Comfort::COMFORT_CAT_SPORT => Yii::t('app', 'Спорт жана эс алуу'),
                Comfort::COMFORT_CAT_GENERAL => Yii::t('app', 'Жалпы'),
                Comfort::COMFORT_CAT_POOL => Yii::t('app', 'Бассейн жана пляж'),
                Comfort::COMFORT_CAT_CHILDREN => Yii::t('app', 'Балдар үчүн'),
                Comfort::COMFORT_CAT_WORK => Yii::t('app', 'Иш үчүн'),
                Comfort::COMFORT_CAT_AVAILABILITY => Yii::t('app', 'Жеткиликтүүлүк'),
                Comfort::COMFORT_CAT_ANIMALS => Yii::t('app', 'Жаныбарлар'),
                Comfort::COMFORT_CAT_INTERNET => Yii::t('app', 'Интернет'),
                Comfort::COMFORT_CAT_BEATY => Yii::t('app', 'Сулуулук жана ден соолук'),
                Comfort::COMFORT_CAT_PARKING => Yii::t('app', 'Токтотуучу жай'),
                Comfort::COMFORT_CAT_STAFF_SPEAKS => Yii::t('app', 'Кызматкерлер сүйлөйт'),
                Comfort::COMFORT_CAT_TRANSFER => Yii::t('app', 'Трансфер'),
                Comfort::COMFORT_CAT_SANITAR => Yii::t('app', 'Санитардык чаралар'),
                Comfort::COMFORT_CAT_WINTER_SPORTS => Yii::t('app', 'Кышкы спорт түрлөрү'),
            ],
            'en' => [
                Comfort::COMFORT_CAT_SERVICE => Yii::t('app', 'Services'),
                Comfort::COMFORT_CAT_SPORT => Yii::t('app', 'Sports and recreation'),
                Comfort::COMFORT_CAT_GENERAL => Yii::t('app', 'General'),
                Comfort::COMFORT_CAT_POOL => Yii::t('app', 'Pool and beach'),
                Comfort::COMFORT_CAT_CHILDREN => Yii::t('app', 'For children'),
                Comfort::COMFORT_CAT_WORK => Yii::t('app', 'For work'),
                Comfort::COMFORT_CAT_AVAILABILITY => Yii::t('app', 'Accessibility'),
                Comfort::COMFORT_CAT_WINTER_SPORTS => Yii::t('app', 'Winter sports'),
                Comfort::COMFORT_CAT_ANIMALS => Yii::t('app', 'Animals'),
                Comfort::COMFORT_CAT_INTERNET => Yii::t('app', 'Internet'),
                Comfort::COMFORT_CAT_BEATY => Yii::t('app', 'Beauty and health'),
                Comfort::COMFORT_CAT_PARKING => Yii::t('app', 'Parking'),
                Comfort::COMFORT_CAT_STAFF_SPEAKS => Yii::t('app', 'Staff speaks'),
                Comfort::COMFORT_CAT_TRANSFER => Yii::t('app', 'Transfer'),
                Comfort::COMFORT_CAT_SANITAR => Yii::t('app', 'Sanitary measures'),
            ]
        ];
        return $arr;
    }


    public function actionRoomComfortTitle()
    {
        $arr = [
            'ru' => [
                RoomComfort::ROOM_COMFORT_SPORT => Yii::t('app', 'Спорт и отдых'),
                RoomComfort::ROOM_COMFORT_GENERAL => Yii::t('app', 'Общее'),
                RoomComfort::ROOM_COMFORT_BATHROOM => Yii::t('app', 'Ванная'),
                RoomComfort::ROOM_COMFORT_INSIDE => Yii::t('app', 'В номерах'),
                RoomComfort::ROOM_COMFORT_EXTRA => Yii::t('app', 'Дополнительно'),
                RoomComfort::ROOM_COMFORT_MEAL => Yii::t('app', 'Питание'),
                RoomComfort::ROOM_COMFORT_OUTSIDE => Yii::t('app', 'Вне помещения и вид'),
                RoomComfort::ROOM_COMFORT_LAUNDRY => Yii::t('app', 'Стирка'),
                RoomComfort::ROOM_COMFORT_POOL => Yii::t('app', 'Бассейн и пляж'),
                RoomComfort::ROOM_COMFORT_INTERNET => Yii::t('app', 'Интернет'),
                RoomComfort::ROOM_COMFORT_BEAUTY => Yii::t('app', 'Красота и здоровье'),
                RoomComfort::ROOM_COMFORT_TV => Yii::t('app', 'Телевидение и техника'),
            ],
            'en' => [
                RoomComfort::ROOM_COMFORT_SPORT => Yii::t('app', 'Sport and recreation'),
                RoomComfort::ROOM_COMFORT_GENERAL => Yii::t('app', 'General'),
                RoomComfort::ROOM_COMFORT_BATHROOM => Yii::t('app', 'Bathroom'),
                RoomComfort::ROOM_COMFORT_INSIDE => Yii::t('app', 'In-room facilities'),
                RoomComfort::ROOM_COMFORT_EXTRA => Yii::t('app', 'Additional'),
                RoomComfort::ROOM_COMFORT_MEAL => Yii::t('app', 'Meal'),
                RoomComfort::ROOM_COMFORT_OUTSIDE => Yii::t('app', 'Outdoor and view'),
                RoomComfort::ROOM_COMFORT_LAUNDRY => Yii::t('app', 'Laundry'),
                RoomComfort::ROOM_COMFORT_POOL => Yii::t('app', 'Pool and beach'),
                RoomComfort::ROOM_COMFORT_INTERNET => Yii::t('app', 'Internet'),
                RoomComfort::ROOM_COMFORT_BEAUTY => Yii::t('app', 'Beauty and health'),
                RoomComfort::ROOM_COMFORT_TV => Yii::t('app', 'Television and appliances'),
            ],
            'ky' => [
                RoomComfort::ROOM_COMFORT_SPORT => Yii::t('app', 'Спорт жана эс алуу'),
                RoomComfort::ROOM_COMFORT_GENERAL => Yii::t('app', 'Жалпы'),
                RoomComfort::ROOM_COMFORT_BATHROOM => Yii::t('app', 'Ванна бөлмөсү'),
                RoomComfort::ROOM_COMFORT_INSIDE => Yii::t('app', 'Бөлмөдөгү ыңгайлуулуктар'),
                RoomComfort::ROOM_COMFORT_EXTRA => Yii::t('app', 'Кошумча'),
                RoomComfort::ROOM_COMFORT_MEAL => Yii::t('app', 'Тамак-аш'),
                RoomComfort::ROOM_COMFORT_OUTSIDE => Yii::t('app', 'Сыртта жана көрүнүш'),
                RoomComfort::ROOM_COMFORT_LAUNDRY => Yii::t('app', 'Жуу жана тазалоо'),
                RoomComfort::ROOM_COMFORT_POOL => Yii::t('app', 'Бассейн жана пляж'),
                RoomComfort::ROOM_COMFORT_INTERNET => Yii::t('app', 'Интернет'),
                RoomComfort::ROOM_COMFORT_BEAUTY => Yii::t('app', 'Сулуулук жана ден соолук'),
                RoomComfort::ROOM_COMFORT_TV => Yii::t('app', 'Телевидение жана техника'),
            ]
        ];
        return $arr;
    }

    private function areDatesOverlapping($start1, $end1, $start2, $end2)
    {
        $start1 = strtotime($start1);
        $end1 = strtotime($end1);
        $start2 = strtotime($start2);
        $end2 = strtotime($end2);

        return $start1 <= $end2 && $start2 <= $end1;
    }



    public function actionView($id)
    {
        $client = Yii::$app->meili->connect();
        $document = $client->index('object')->getDocument($id);
        return $document;
    }

    public function actionSimilar($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Step 1: Get the target object by ID
        $targetHit = $index->search('', [
            'filter' => 'id = ' . (int) $id,
            'limit' => 1,
        ])->getHits();

        if (empty($targetHit)) {
            throw new \yii\web\NotFoundHttpException('Object not found');
        }

        $target = $targetHit[0];

        // Step 2: Get city list from target and normalize it
        $targetCities = array_map('mb_strtolower', $target['city'] ?? []);
        $targetCities = array_filter($targetCities);

        // Step 3: Calculate from_price of the target object
        $targetPrice = null;
        if (!empty($target['rooms'])) {
            $minPrice = PHP_FLOAT_MAX;
            foreach ($target['rooms'] as $room) {
                if (isset($room['tariff'])) {
                    foreach ($room['tariff'] as $tariff) {
                        if (isset($tariff['prices'])) {
                            foreach ($tariff['prices'] as $priceData) {
                                if (!empty($priceData['price_arr']) && is_array($priceData['price_arr'])) {
                                    foreach ($priceData['price_arr'] as $p) {
                                        if ($p < $minPrice) {
                                            $minPrice = $p;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $targetPrice = $minPrice === PHP_FLOAT_MAX ? null : $minPrice;
        }

        // if ($targetPrice === null) {
        //     throw new \yii\web\BadRequestHttpException('Target object has no valid price');
        // }

        // Step 4: Fetch all other objects
        $searchResults = $index->search('', [
            'limit' => 1000 // Cap for now
        ]);

        $hits = $searchResults->getHits();
        $similar = [];

        foreach ($hits as $hit) {
            if ($hit['id'] == $id)
                continue;

            // Check if city matches
            $cityMatch = false;
            if (!empty($hit['city']) && is_array($hit['city'])) {
                foreach ($hit['city'] as $cityName) {
                    if (in_array(mb_strtolower($cityName), $targetCities, true)) {
                        $cityMatch = true;
                        break;
                    }
                }
            }

            if (!$cityMatch)
                continue;

            // Calculate from_price
            $minPrice = PHP_FLOAT_MAX;
            if (!empty($hit['rooms'])) {
                foreach ($hit['rooms'] as $room) {
                    if (isset($room['tariff'])) {
                        foreach ($room['tariff'] as $tariff) {
                            if (isset($tariff['prices'])) {
                                foreach ($tariff['prices'] as $priceData) {
                                    if (!empty($priceData['price_arr'])) {
                                        foreach ($priceData['price_arr'] as $p) {
                                            if ($p < $minPrice) {
                                                $minPrice = $p;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($minPrice !== PHP_FLOAT_MAX) {
                $hit['from_price'] = $minPrice;
                $similar[] = $hit;
            }
        }

        // Step 5: Separate cheaper and more expensive
        $cheaper = array_filter($similar, fn($h) => $h['from_price'] < $targetPrice);
        $expensive = array_filter($similar, fn($h) => $h['from_price'] > $targetPrice);

        usort($cheaper, fn($a, $b) => $b['from_price'] <=> $a['from_price']); // descending
        usort($expensive, fn($a, $b) => $a['from_price'] <=> $b['from_price']); // ascending

        // Step 6: Assemble result with fallback logic
        $result = [];

        if (count($cheaper) >= 2 && count($expensive) >= 2) {
            $result = array_merge(array_slice($cheaper, 0, 2), array_slice($expensive, 0, 2));
        } elseif (count($cheaper) < 2 && count($expensive) >= (4 - count($cheaper))) {
            $result = array_merge($cheaper, array_slice($expensive, 0, 4 - count($cheaper)));
        } elseif (count($expensive) < 2 && count($cheaper) >= (4 - count($expensive))) {
            $result = array_merge(array_slice($cheaper, 0, 4 - count($expensive)), $expensive);
        } else {
            $result = array_merge(array_slice($cheaper, 0, 4));
        }

        return [
            'target_price' => $targetPrice,
            'data' => $result
        ];
    }




    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    public function lastIncrement()
    {
        try {
            $client = Yii::$app->meili->connect();
            $searchResults = $client->index('object')->search('', [
                'sort' => ['id:desc'],
                'limit' => 1
            ]);
            if (!empty($searchResults->getHits())) {
                $lastDocument = $searchResults->getHits()[0];
                return $lastDocument['id'];
            }

            return 0; // Return 0 if no documents found

        } catch (\Exception $e) {
            Yii::error("Meilisearch error: " . $e->getMessage());
            return $e->getMessage();
        }
    }


    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionActivate()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');

        $model = $this->findModel($id);

        if ($model->activate()) {
            $response["success"] = true;
            $response["message"] = 'Объявление активировано.';
        } else {
            $response["errors"] = 'Не удалось активировать объявление';
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeactivate()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');
        $model = $this->findModel($id);

        if ($model->deactivate()) {
            $response["success"] = true;
            $response["message"] = 'Объявление деактивировано.';
        } else {
            $response["errors"] = 'Не удалось деактивировать объявление';
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUp()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');

        $model = $this->findModel($id);

        if ($model->up()) {
            $response["success"] = true;
            $response["message"] = 'Объявление поднято.';
        } else {
            $response["errors"] = 'Поднимать объявление возможно через какждые 3 часа';
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRemoveImages()
    {
        $response = [];
        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $images = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'images');

        $post = $this->findModel($post_id);

        foreach ($images as $item) {
            $mainImageId = $post->getImage()->id;

            foreach ($post->getImages() as $image) {
                if ($image->id == $item) {
                    $post->removeImage($image);
                    $response[$item]["item"] = $item;
                    $response[$item]["success"] = true;
                }
            }

            if ($mainImageId == $item) {
                foreach ($post->getImages() as $image) {
                    if (!is_a($image, PlaceHolder::className())) {
                        $post->setMainImage($image);
                        break;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRemoveImage()
    {
        $response["success"] = false;

        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $image_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'image_id');

        $post = $this->findModel($post_id);
        $mainImageId = $post->getImage()->id;

        foreach ($post->getImages() as $image) {
            if ($image->id == $image_id) {
                $post->removeImage($image);
                $response["success"] = true;
                $response["message"] = 'Изображение удалено.';
                $response["post"] = $post;
            }
        }

        if ($mainImageId == $image_id) {
            foreach ($post->getImages() as $image) {
                if (!is_a($image, PlaceHolder::className())) {
                    $post->setMainImage($image);
                    break;
                }
            }
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionSetImageAsMain()
    {
        $response["success"] = false;

        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $image_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'image_id');

        $uploadedMainImage = UploadedFile::getInstanceByName('newmainimage');
        $post = $this->findModel($post_id);

        if ($uploadedMainImage) {
            $path = Yii::getAlias('@webroot/uploads/images/store/') . time() . '.' . $uploadedMainImage->extension;
            $uploadedMainImage->saveAs($path);
            $response["asdf"] = $post->attachImage($path, true);
            @unlink($path);
            $response["success"] = true;
            $response["message"] = 'Изображение сделано главным.';
            $response["post"] = $post;
        } else if ($image_id) {
            foreach ($post->getImages() as $image) {
                if ($image->id == $image_id) {
                    if (!is_a($image, PlaceHolder::className())) {
                        $post->setMainImage($image);
                        $response["success"] = true;
                        $response["message"] = 'Изображение сделано главным.';
                        $response["post"] = $post;
                    }
                }
            }
            if (!isset($response['message'])) {
                $response["message"] = 'image_id не найден в имеющих фото';
            }
        } else {
            $response["message"] = 'отсутствует newmainimage или image_id';
        }

        return $response;
    }

    public function actionUpnote()
    {
        $req = Yii::$app->request->post();
        $uid = Yii::$app->user->id;
        if ($uid && isset($req['note'])) {
            if (!empty($req['id'])) {
                $model = Note::find()->where(['id' => $req['id'], 'user_id' => $uid])->one();
                if ($model && empty($req['note'])) {
                    $model->delete();
                    return true;
                }
                if (!$model) {
                    return null;
                }
            } else if (!empty($req['post_id'])) {
                $model = Note::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();
                if (!$model) {
                    $model = new Note();
                    $model->user_id = $uid;
                    $model->post_id = $req['post_id'];
                }
            }
            if ($model) {
                $model->note = $req['note'];
                $model->save();
                return $model;
            }
        }
    }

    public function actionNote()
    {
        $req = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        if ($uid && !empty($req['post_id'])) {
            $dao = Yii::$app->db;
            //$note = $dao->createCommand("SELECT * FROM `note` WHERE post_id={$req['post_id']} AND user_id={$uid}")->queryOne();
            $note = Note::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();
            if ($note) {
                return $note;
            }
        }
        return null;
    }

    public function actionRemoveRate()
    {
        $req = Yii::$app->request->post();
        $response["success"] = false;

        $model = Rating::find()->where(['id' => $req['id']])->one();
        if ($model) {
            if ($model->delete()) {
                $response["success"] = true;
                $response["message"] = "Отзыв удален";
            }
        }
        return $response;
    }

    public function actionUprate()
    {
        $req = Yii::$app->request->post();
        $uid = Yii::$app->user->id;
        if ($uid && !empty($req['post_id']) && isset($req['rate'])) {
            if (!empty($req['id'])) {
                $model = Rating::find()->where(['id' => $req['id'], 'user_id' => $uid])->one();
                if (!$model) {
                    return null;
                }
            } else {
                $model = Rating::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();

                if (!$model) {
                    $model = new Rating();
                    $model->user_id = $uid;
                    $model->post_id = $req['post_id'];
                }
            }

            $receiver_id = Post::findOne($req['post_id'])->user_id;
            $model->receiver_id = $receiver_id;
            $model->note = isset($req['note']) ? $req['note'] : '';
            $model->rate = (int) $req['rate'];
            $model->full_name = $req['name'];

            if ($model->save()) {
                $app_id = 0;
                $text = "Добавлен комментарий к вашему посту";
                $data = [
                    'id' => (string) $req['post_id'],
                    'text' => (string) $text
                ];

                $params = [
                    'notif' => ['title' => 'Внимание', 'body' => $text],
                    'data' => $data,
                ];

                $token_rows = Yii::$app->db->createCommand("SELECT * FROM fcm_token WHERE user_id='{$receiver_id}'")->queryAll();
                if ($token_rows) {
                    $arr = [];
                    foreach ($token_rows as $item) {
                        $token = (string) $item['token'];
                        Yii::$app->firebase->sendPushNotification($token, $params['title'], $params['body'], $data);
                    }
                }
            }
            return $model;
        }
    }

    public function actionRatings()
    {
        $req = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        if (!empty($req['post_id'])) {
            return Rating::find()->where(['post_id' => $req['post_id']])->all();
        }
        return [];
    }




    protected static function attr()
    {
        $dao = Yii::$app->db;
        $rows = $dao->createCommand("SELECT id,title,`type` FROM `category_attribute`")->queryAll();
        return ['titles' => ArrayHelper::map($rows, 'id', 'title'), 'types' => ArrayHelper::map($rows, 'id', 'type')];
    }

    protected static function options()
    {
        $dao = Yii::$app->db;
        $rows = $dao->createCommand("SELECT id,`value` FROM `directory_option`")->queryAll();
        return ArrayHelper::map($rows, 'id', 'value');
    }

    /* protected static function ctgTitles()
    {
        $dao = Yii::$app->db;
        return $dao->createCommand("SELECT id,title,title_ky FROM `category`")->queryAll();
    } */

    public function actionSaveSearch()
    {
        $uid = Yii::$app->user->id;
        $qp = Yii::$app->request->queryParams;
        $qs = Yii::$app->request->queryString;
        /* if (isset($qp['PostSearch'])) {
            $json = json_encode($qp['PostSearch']);
        } else {
            $json = json_encode($qp);
        } */

        if (!empty($qp['PostSearch']['ctg_ids'])) {
            $ctg_id = $qp['PostSearch']['ctg_ids'][0];
            $qp['categoriesChain'] = Post::getCategoriesChainStatic($ctg_id);
            if (($key = array_search($ctg_id, $qp['categoriesChain'])) !== false) {
                unset($qp['categoriesChain'][$key]);
            }
        }

        $json = json_encode($qp);
        $td = self::titleDesc($qp);
        $model = new SavedSearch();
        $model->title = $td['title'];
        $model->description = $td['desc'];
        $model->paramstr = $qs;
        $model->paramjsn = $json;
        $model->user_id = $uid;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
        return null;
    }

    public function actionSearches()
    {
        $uid = Yii::$app->user->id;
        return SavedSearch::find()->where(['user_id' => $uid])->all();
    }

    public function actionDeleteSearch()
    {
        $id = Yii::$app->request->post('id');
        $uid = Yii::$app->user->id;
        $isDel = SavedSearch::find()->where(['user_id' => $uid, 'id' => $id])->one()->delete();
        if ($isDel) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    public function actionSetDiscount()
    {
        $response["success"] = false;
        $items = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post');
        $client = Yii::$app->meili->connect();
        foreach ($items as $val) {
            $post = Post::findOne($val['id']);
            $post->discount = $val['discount'];
            if ($post->update()) {
                $response['success'] = true;
                $arr = ['id' => $val['id'], 'discount' => $val['discount']];
                $client->index('posts')->updateDocuments($arr);
            } else {
                $response['success'] = false;
            }
        }
        return $response;
    }

    public function actionTestDiscount()
    {
        $response["success"] = false;
        $items = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'order');
        echo "<pre>";
        print_r($items);
        echo "</pre>";
        die();
    }


    public function actionSaveSearchGuest()
    {
        $qp = Yii::$app->request->queryParams;
        $qs = Yii::$app->request->queryString;
        /* if (isset($qp['PostSearch'])) {
            $json = json_encode($qp['PostSearch']);
        } else {
            $json = json_encode($qp);
        } */
        if (!empty($qp['PostSearch']['ctg_ids'])) {
            $ctg_id = $qp['PostSearch']['ctg_ids'][0];
            $qp['categoriesChain'] = Post::getCategoriesChainStatic($ctg_id);
            if (($key = array_search($ctg_id, $qp['categoriesChain'])) !== false) {
                unset($qp['categoriesChain'][$key]);
            }
        }
        $json = json_encode($qp);
        $td = self::titleDesc($qp);
        if (!empty($td['title'])) {
            return ['title' => $td['title'], 'description' => $td['desc'], 'paramstr' => $qs, 'paramjsn' => $json];
        } else {
            return null;
        }
    }



    /**
     * Finds the Favorite model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $post_id
     * @return Favorite the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findFavorite($post_id)
    {
        if (($model = Favorite::find()->where(['user_id' => Yii::$app->user->id, 'post_id' => $post_id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Избранное не найдено.');
    }


    public function actionExchange()
    {
        $url = 'http://data.fx.kg/api/v1/current';
        $bearerToken = 'axqspIDnS9HscCFcGK6f5WZMnD3DrOcwBMWEsOx957a78122'; // Replace with your actual token
        $targetId = 2; // The ID you want to filter

        $client = new \yii\httpclient\Client();

        try {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->addHeaders(['Authorization' => "Bearer $bearerToken"])
                ->send();

            if ($response->isOk) {
                $data = $response->data; // JSON decoded response

                // Filter to get only the object with id = 2
                $filteredData = array_filter($data, function ($item) use ($targetId) {
                    return $item['id'] == $targetId;
                });

                // Reset array keys and return the first matched object
                return $this->asJson(array_values($filteredData)[0] ?? ['error' => 'Not found']);
            } else {
                return $this->asJson(['error' => 'Failed to fetch data', 'status' => $response->statusCode]);
            }
        } catch (\Exception $e) {
            return $this->asJson(['error' => $e->getMessage()]);
        }
    }





    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Post::findOne($id);
        if ($model) {
            if ($model->moderation_status === Post::MODERATION_STATUS_APPROVED) {
                return $model;
            } elseif ($model->user_id == Yii::$app->user->id) {
                return $model;
            } else {
                if (Yii::$app->user->can('updatePost', ['post' => $model])) {
                    return $model;
                }
            }
            return $model;
        } else {

            throw new NotFoundHttpException('Объявление не найдено.');
        }
    }
}

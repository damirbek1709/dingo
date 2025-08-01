<?php

namespace app\modules\owner\controllers;

use app\models\RoomComfort;
use rico\yii2images\models\Image;
use Yii;
use app\models\Objects;
use app\models\Comfort;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\web\UploadedFile;
use app\models\PaymentType;
use app\models\RoomCat;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\Tariff;
use app\models\TariffSearch;
use app\models\Booking;
use app\models\BookingSearch;
use yii\helpers\Json;
use Exception;
use app\models\user\User;
/**
 * EventController implements the CRUD actions for Event model.
 */
class ObjectController extends Controller
{
    public $layout = "main";
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['room-list', 'update', 'tariff-list', 'room', 'view', 'delete', 'comfort', 'index-admin', 'file-upload', 'finances'],
                    'roles' => ['admin'],
                ],
                [
                    'allow' => true,
                    'actions' => ['room-list', 'edit-room', 'add-room', 'delete', 'delete-room', 'finances'],
                    'roles' => ['owner'], // Authenticated users
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('object_id');
                        $client = Yii::$app->meili->connect();
                        $object = $client->index('object')->getDocument($object_id);

                        if ($object['user_id'] === Yii::$app->user->id) {
                            return true;
                        }
                        return false;
                    }
                ],

                [
                    'allow' => true,
                    'actions' => ['add-room', 'edit-room', 'room-beds'],
                    'roles' => ['admin', 'owner'], // Authenticated users
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('object_id');
                        $client = Yii::$app->meili->connect();
                        $object = $client->index('object')->getDocument($object_id);
                        if ($object['user_id'] === Yii::$app->user->id) {
                            return true;
                        }
                        return false;
                    }
                ],

                [
                    'allow' => true,
                    'actions' => ['view', 'comfort', 'payment', 'terms', 'room-beds'],
                    'roles' => ['admin'], // Authenticated users

                ],

                [
                    'allow' => true,
                    'actions' => ['comfort', 'payment', 'terms', 'room-list', 'add-room', 'update', 'delete', 'file-upload', 'room-beds', 'search-regions'],
                    'roles' => ['owner', 'admin'],
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('object_id');
                        $client = Yii::$app->meili->connect();
                        $object = $client->index('object')->getDocument($object_id);
                        if ($object['user_id'] === Yii::$app->user->id) {
                            return true;
                        }
                        return false;
                    }
                ],

                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => ['owner', 'admin'],
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('object_id');
                        $new_object_data = Yii::$app->session->get('new_object_data');
                        if ($new_object_data) {
                            $object = $new_object_data;
                            if ($object['id'] == $object_id) {
                                return true;
                            }
                        } else {
                            $client = Yii::$app->meili->connect();
                            $object = $client->index('object')->getDocument($object_id);
                            if ($object['user_id'] === Yii::$app->user->id) {
                                return true;
                            }
                        }
                        return false;
                    }
                ],

                [
                    'allow' => true,
                    'actions' => ['room', 'edit-room', 'add-tariff', 'edit-tariff', 'tariff-list', 'room-comfort', 'pictures'],
                    'roles' => ['owner'],
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('object_id');
                        $client = Yii::$app->meili->connect();
                        $object = $client->index('object')->getDocument($object_id);
                        if ($object['user_id'] === Yii::$app->user->id) {
                            return true;
                        }
                        return false;
                    }
                ],

                [
                    'allow' => true,
                    'actions' => ['index', 'create', 'add-tariff', 'edit-tariff', 'prices', 'remove-object-image', 'remove-room-image', 'remove-file', 'send-to-moderation', 'unpublish', 'finances'],
                    'roles' => ['admin', 'owner'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'change-status' => ['POST'],
                'add-order' => ['POST'],
            ],
        ];

        return $behaviors;
    }
    /**
     * Lists all Event models.
     * @return mixed
     */
    public function actionAdmin()
    {
        $filter_string = "user_id=" . Yii::$app->user->id;
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search('', [
            'filter' => [
                $filter_string
            ],
            'limit' => 10000
        ]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $res->getHits(),
            'pagination' => [
                'pageSize' => 12, // Adjust page size as needed
            ],
            // 'sort' => [
            //     'attributes' => ['id', 'name', 'email'], // Sortable attributes
            // ],
        ]);

        return $this->render('admin', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionIndex()
    {
        $filter_string = "user_id=" . Yii::$app->user->id;
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search('', [
            'filter' => [
                $filter_string
            ],
            'limit' => 10000
        ]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $res->getHits(),
            'pagination' => [
                'pageSize' => 12, // Adjust page size as needed
            ],
            // 'sort' => [
            //     'attributes' => ['id', 'name', 'email'], // Sortable attributes
            // ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionIndexAdmin()
    {
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search('', [
            'limit' => 10000
        ]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $res->getHits(),
            'pagination' => [
                'pageSize' => 12, // Adjust page size as needed
            ],
            'sort' => [
                'attributes' => ['id'], // Sortable attributes
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionRoomList($object_id)
    {
        $id = $object_id;
        $this->layout = "main";
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($id);
        $rooms = [];

        $model = new Objects($object);
        if (array_key_exists('rooms', $object)) {
            $rooms = $object['rooms'];
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $rooms,
            'pagination' => [
                'pageSize' => 12, // Adjust page size as needed
            ],
            // 'sort' => [
            //     'attributes' => ['id', 'name', 'email'], // Sortable attributes
            // ],
        ]);

        return $this->render('rooms/index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'rooms' => $rooms,
            'object_id' => $object_id,
            'object_title' => $object['name'][0],
        ]);
    }

    public function roomTariffList($object_id, $room_id)
    {
        $this->layout = "main";
        $tariff = [];
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);
        $rooms = [];

        if (array_key_exists('rooms', $object)) {
            $roomData = [];
            foreach ($object['rooms'] as $room) {
                if ($room['id'] == $room_id) {
                    $roomData = $room;
                    break;
                }
            }


            if (array_key_exists('tariff', $roomData)) {
                $tariff = $roomData['tariff'];
            }
        }

        return $this->render('/tariff/index', [
            'list' => $tariff,
            'object_title' => $object['name'][0],
            'object_id' => $object_id
        ]);
    }

    public function actionTariffList($object_id)
    {
        $this->layout = "main";

        $searchModel = new TariffSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['object_id' => $object_id]);

        // Load the object from the database
        $model = Objects::findOne($object_id);

        return $this->render('/tariff/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'object_id' => $object_id,
        ]);
    }


    public function actionPrices($object_id)
    {
        $id = $object_id;
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($id);
        $rooms = [];

        $model = new Objects($object);
        if (array_key_exists('rooms', $object)) {
            $rooms = $object['rooms'];
        }
        return $this->render('prices_test', [
            'rooms' => $rooms,
            'object_id' => $id,
            'model' => $model,
        ]);
    }


    public function actionView($object_id)
    {
        $new_object_data = Yii::$app->session->get('new_object_data');

        if ($new_object_data && $new_object_data['id'] == $object_id) {
            // ✅ Convert array to model instance
            $model = new Objects($new_object_data);
            Yii::$app->session->remove('new_object_data');
        } else {
            $client = Yii::$app->meili->connect();
            $index = $client->index('object');
            $searchResult = $index->getDocument($object_id);

            if (empty($searchResult)) {
                throw new NotFoundHttpException('Record not found.');
            }

            // ✅ Meilisearch result is an object/array, wrap it too
            $model = new Objects($searchResult);
        }

        $bind_model = Objects::find()->where(['id' => $object_id])->one();

        return $this->render('view', [
            'model' => $model,
            'bind_model' => $bind_model,
            'object_id' => $object_id
        ]);
    }

    function translateOblast($oblast)
    {
        switch ($oblast) {
            case "Бишкек":
                return ["Bishkek", "Бишкек"];
                break;
            case "Ошская область":
                return ["Osh oblast", "Ош областы"];
                break;
            case "Чуйская область":
                return ["Chui oblast", "Чүй областы"];
                break;
            case "Джалал-Абадская область":
                return ["Jalal-Abad oblast", "Жалал-Абад областы"];
                break;
            case "Нарынская область":
                return ["Naryn oblast", "Нарын областы"];
                break;
            case "Таласская область":
                return ["Talas oblast", "Талас областы"];
                break;
            case "Иссык-Кульская область":
                return ["Yssyk-Kul oblast", "Ысык-Көл областы"];
                break;
            case "Баткенская область":
                return ["Batken oblast", "Баткен областы"];
                break;
            default:
                return ["", ""];

        }
    }

    /**
     * Creates a new Event model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $model = new Objects();
        $model->link_id = 1;

        // Init city text for Select2 (used if validation fails)
        $initCityText = '';

        if ($model->load($this->request->post())) {
            // Fetch city display name even before validation (to preserve on re-render)
            if ($model->city_id) {
                try {
                    $city = Yii::$app->meili->connect()->index('region')->getDocument($model->city_id);
                    $initCityText = $city['name'] ?? '';
                    if (!empty($city['region'])) {
                        $initCityText .= ' (' . $city['region'] . ')';
                    }
                } catch (\Throwable $e) {
                    Yii::warning("Meilisearch city fetch failed: " . $e->getMessage(), 'meilisearch');
                }
            }

            // Continue with validation and save logic
            if ($model->validate()) {
                if ($model->city_id && isset($city)) {
                    $model->city = [
                        $city['name'] ?? '',
                        $city['name_en'] ?? '',
                        $city['name_kg'] ?? '',
                    ];
                    $model->oblast_id = [
                        $city['region'] ?? '',
                        $this->translateOblast($city['region'])[0],
                        $this->translateOblast($city['region'])[1]
                    ];
                }

                if ($model->save(false)) {
                    $model->images = UploadedFile::getInstances($model, 'images');
                    if ($model->images) {
                        foreach ($model->images as $image) {
                            $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                            $image->saveAs($path);
                            $model->attachImage($path, true);
                            @unlink($path);
                        }
                    }

                    $model->ceo_doc = UploadedFile::getInstance($model, 'ceo_doc');
                    $model->financial_doc = UploadedFile::getInstance($model, 'financial_doc');

                    if ($model->ceo_doc) {
                        $dir = Yii::getAlias('@webroot/uploads/documents/' . $model->id . '/ceo');
                        if (!is_dir($dir)) {
                            FileHelper::createDirectory($dir);
                        }
                        $fileName = 'ceo_doc_' . time() . '.' . $model->ceo_doc->extension;
                        $uploadPath = $dir . '/' . $fileName;
                        if ($model->ceo_doc->saveAs($uploadPath)) {
                            $model->ceo_doc = $fileName;
                        }
                    }

                    if ($model->financial_doc) {
                        $dir = Yii::getAlias('@webroot/uploads/documents/' . $model->id . '/financial');
                        if (!is_dir($dir)) {
                            FileHelper::createDirectory($dir);
                        }
                        $fileName = 'financial_doc_' . time() . '.' . $model->financial_doc->extension;
                        $uploadPath = $dir . '/' . $fileName;
                        if ($model->financial_doc->saveAs($uploadPath)) {
                            $model->financial_doc = $fileName;
                        }
                    }

                    // Push to Meilisearch
                    $object_arr = [
                        'id' => $model->id,
                        'name' => array_values([$model->name, $model->name_en, $model->name_ky]),
                        'type' => (int) $model->type,
                        'reception' => (int) $model->reception,
                        'city' => $model->city,
                        'address' => [$model->address, $model->address_en, $model->address_ky],
                        'description' => [
                            "<div>" . $model->description . "</div>",
                            "<div>" . $model->description_en . "</div>",
                            "<div>" . $model->description_ky . "</div>"
                        ],
                        'currency' => $model->currency,
                        'phone' => $model->phone,
                        'site' => $model->site,
                        'check_in' => $model->check_in,
                        'check_out' => $model->check_out,
                        'lat' => (float) $model->lat,
                        'lon' => (float) $model->lon,
                        'early_check_in' => (bool) $model->early_check_in,
                        'late_check_in' => (bool) $model->late_check_in,
                        'internet_public' => (bool) $model->internet_public,
                        'user_id' => (int) Yii::$app->user->id,
                        'email' => $model->email,
                        'features' => $model->features ?? [],
                        'images' => $model->getPictures(),
                        'general_room_count' => (int) $model->general_room_count,
                        'status' => 0,
                        'city_id' => (int) $model->city_id,
                        'oblast_id' => $model->oblast_id,
                    ];

                    if ($index->addDocuments($object_arr)) {
                        Yii::$app->session->set('new_object_data', $object_arr);
                        return $this->redirect(['view', 'object_id' => $model->id]);
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'initCityText' => $initCityText, // ✅ needed for Select2 on re-render
        ]);
    }


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
     * Updates an existing Event model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Fetch record from Meilisearch
        $searchResult = $index->getDocument($object_id);

        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        // Convert the first result into a model
        $model = new Objects($searchResult);
        $bind_model = Objects::findOne($object_id);

        // Handle form submission
        if ($model->load(Yii::$app->request->post())) {
            $model->type = (int) $model->type;
            $model->lat = (float) $model->lat;
            $model->lon = (float) $model->lon;
            $model->user_id = (int) Yii::$app->user->id;

            $request = Yii::$app->request->post();

            $model->name = [
                $request['Objects']['name'] ?? '',
                $request['Objects']['name_en'] ?? '',
                $request['Objects']['name_ky'] ?? '',
            ];

            if ($model->city_id) {
                $client = Yii::$app->meili->connect();
                $city = $client->index('region')->getDocument($model->city_id);
                $model->city = [
                    $city['name'] ?? '',
                    $city['name_en'] ?? '',
                    $city['name_kg'] ?? '',
                ];
                $model->oblast_id = [
                    $city['region'] ?? '',
                    $this->translateOblast($city['region'])[0],
                    $this->translateOblast($city['region'])[1]
                ];
            }

            $model->address = [
                $request['Objects']['address'] ?? '',
                $request['Objects']['address_en'] ?? '',
                $request['Objects']['address_ky'] ?? '',
            ];

            $model->description = [
                $request['Objects']['description'] ?? '',
                $request['Objects']['description_en'] ?? '',
                $request['Objects']['description_ky'] ?? '',
            ];


            if ($bind_model->save(false) && $model->validate()) {
                $bind_model->ceo_doc = UploadedFile::getInstance($bind_model, 'ceo_doc');
                $bind_model->financial_doc = UploadedFile::getInstance($bind_model, 'financial_doc');
                if ($bind_model->ceo_doc) {
                    $fileName = 'ceo_doc_' . time() . '.' . $bind_model->ceo_doc->extension;
                    $dir = Yii::getAlias('@webroot/uploads/documents/' . $bind_model->id . '/ceo');
                    if (!is_dir($dir)) {
                        FileHelper::createDirectory($dir);
                    }
                    $uploadPath = $dir . '/' . $fileName;

                    if ($bind_model->ceo_doc->saveAs($uploadPath)) {
                        $bind_model->ceo_doc = $fileName; // Save file name into DB (optional)
                    }
                }

                if ($bind_model->financial_doc) {
                    $fileName = 'financial_doc_' . time() . '.' . $bind_model->financial_doc->extension;
                    $dir = Yii::getAlias('@webroot/uploads/documents/' . $bind_model->id . '/financial');
                    if (!is_dir($dir)) {
                        FileHelper::createDirectory($dir);
                    }
                    $uploadPath = $dir . '/' . $fileName;
                    if ($bind_model->financial_doc->saveAs($uploadPath)) {
                        $bind_model->financial_doc = $fileName; // Save file name into DB (optional)
                    }
                }

                $bind_model->images = UploadedFile::getInstances($model, 'images');
                if ($bind_model->images) {
                    $mainTempId = $model->img; // e.g., 'new_filename_xyz' or existing ID
                    $mainImageSet = false;

                    foreach ($bind_model->images as $image) {
                        // Match the frontend temp ID logic
                        $baseNameSanitized = preg_replace('/\W+/', '_', $image->name);
                        $possibleTempIdPrefix = 'new_' . $baseNameSanitized;

                        $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;

                        if ($image->saveAs($path)) {
                            // Match full frontend-generated ID prefix
                            $isMain = false;
                            if (!$mainImageSet && strpos($mainTempId, $possibleTempIdPrefix) === 0) {
                                $isMain = true;
                                $mainImageSet = true;
                            }

                            $bind_model->attachImage($path, $isMain);
                            @unlink($path);
                        }
                    }
                }

                $status = Objects::currentStatus($model['id'], $model['status']);


                if ($model->img) {
                    $image_id = $model->img;
                    foreach ($model->getImages() as $image) {
                        if ($image->id == $image_id) {
                            $model->setMainImage($image);
                        }
                    }
                }

                $object_arr = [
                    'id' => (int) $model->id,
                    'name' => $model->name,
                    'type' => (int) $model->type,
                    'reception' => (int) $model->reception,
                    'city' => $model->city,
                    'city_id' => (int) $model->city_id,
                    'address' => $model->address,
                    'description' => $model->description,
                    'currency' => $model->currency,
                    'phone' => $model->phone,
                    'site' => $model->site,
                    'check_in' => $model->check_in,
                    'check_out' => $model->check_out,
                    'lat' => (float) $model->lat,
                    'lon' => (float) $model->lon,
                    'early_check_in' => (bool) $model->early_check_in,
                    'late_check_in' => (bool) $model->late_check_in,
                    'internet_public' => (bool) $model->internet_public,
                    'email' => $model->email,
                    'features' => $model->features ?? [],
                    'images' => $model->getPictures(),
                    'general_room_count' => $model->general_room_count,
                    'oblast_id' => $model->oblast_id,
                    'status'=> $status
                ];

                if (!Yii::$app->user->can('admin')) {
                    $object_arr['user_id'] = (int) Yii::$app->user->id;
                }


                if ($index->updateDocuments($object_arr)) {
                    Yii::$app->session->set('new_object_data', $object_arr);
                    return $this->redirect(['view', 'object_id' => $model->id]);
                }

            }
        }

        return $this->render('update', [
            'model' => $model,
            'id' => $object_id,
            'bindModel' => $bind_model
        ]);
    }

    public function actionComfort($object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $comfort_list = Yii::$app->request->post('comforts');

        // Fetch object from Meilisearch
        $searchResult = $index->getDocument($object_id);
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $objectData = $searchResult;

        // Use session override if present
        $session = Yii::$app->session;
        $sessionKey = "object_{$object_id}_comfort_override";
        $sessionComfortList = $session->get($sessionKey);

        if ($sessionComfortList !== null) {
            $objectData['comfort_list'] = $sessionComfortList;
        }

        $model = new Objects($objectData);

        if (!empty($comfort_list)) {
            $comfortArr = [];

            foreach ($comfort_list as $categoryId => $comforts) {
                foreach ($comforts as $comfortId => $comfortData) {
                    $comfort = Comfort::findOne($comfortId);
                    if ($comfort) {
                        $comfortArr[$categoryId][$comfortId] = [
                            'ru' => $comfort->title,
                            'en' => $comfort->title_en,
                            'ky' => $comfort->title_ky,
                            'is_paid' => isset($comfortData['is_paid']) ? 1 : 0,
                        ];
                    }
                }
            }

            // Update Meilisearch
            $meilisearchData = [
                'id' => (int) $object_id,
                'comfort_list' => $comfortArr
            ];

            if ($index->updateDocuments($meilisearchData)) {
                // ✅ Save comfort_list override to session
                $session->set($sessionKey, $comfortArr);

                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        // ✅ Remove session override after one render
        $session->remove($sessionKey);

        return $this->render('comfort', [
            'model' => $model,
            'id' => $object_id,
            'object_id' => $object_id,
        ]);
    }



    public function actionTerms($object_id)
    {
        $id = $object_id;
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        $new_term_data = Yii::$app->session->get('new_term_data');
        if ($new_term_data && $new_term_data['id'] == $object_id) {
            $model = $new_term_data;
            Yii::$app->session->remove('new_room_data');
        } else {

            // Fetch object from Meilisearch
            $searchResult = $index->getDocument($id);
            if (empty($searchResult)) {
                throw new \yii\web\NotFoundHttpException('Record not found.');
            }

            // Load object data
            $model = new Objects($searchResult);
            $data = $searchResult;

            // Assign saved values to model attributes
            $model->early_check_in = $data['terms']['early_check_in'] ?? false;
            $model->late_check_in = $data['terms']['late_check_in'] ?? false;
            $model->internet_public = $data['terms']['internet_public'] ?? false;
            $model->animals_allowed = $data['terms']['animals_allowed'] ?? false;
            $model->meal_purchaise = $data['terms']['meal_purchaise'] ?? false;

            $model->meal_terms = $data['terms']['meal_terms'] ?? [];
            $model->children = $data['terms']['children'] ?? [];

            if (Yii::$app->request->isPost) {

                $meal_arr = Yii::$app->request->post('meal_terms', []);

                $arr = [];
                if ($meal_arr) {
                    $counter = 0;
                    foreach ($meal_arr as $term) {
                        $temp = Objects::mealTypeFull($term['meal_type']);
                        $arr[$counter]['meal_title'] = $temp;
                        $arr[$counter]['meal_type'] = (int) $term['meal_type'];
                        $arr[$counter]['meal_cost'] = (int) $term['meal_cost'];
                        $counter++;
                    }
                }

                // Save form data
                $model->early_check_in = Yii::$app->request->post('early_check_in', 0);
                $model->late_check_in = Yii::$app->request->post('late_check_in', 0);
                $model->internet_public = Yii::$app->request->post('internet_public', 0);
                $model->animals_allowed = Yii::$app->request->post('animals_allowed', 0);
                $model->meal_terms = $meal_arr;

                $model->children = Yii::$app->request->post('children', 0);
                $model->meal_purchaise = Yii::$app->request->post('meal_purchaise', false);

                // Store in Meilisearch with correct format
                $meilisearchData = [
                    'id' => $id,
                    'terms' => [
                        'early_check_in' => (bool) $model->early_check_in,
                        'late_check_in' => (bool) $model->late_check_in,
                        'internet_public' => (bool) $model->internet_public,
                        'animals_allowed' => (bool) $model->animals_allowed,
                        'meal_terms' => $arr,
                        'meal_purchaise' => (bool) $model->meal_purchaise,
                        'children' => (int) $model->children,
                    ]
                ];

                if ($index->updateDocuments($meilisearchData)) {
                    Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                    Yii::$app->session->set('new_term_data', $model);
                    return $this->refresh();
                }
            }
        }

        return $this->render('terms', [
            'model' => $model,
            'id' => $id,
        ]);
    }

    public function actionSendToModeration()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('object_id');
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);

        if (
            $index->updateDocuments([
                'id' => (int) $id,
                'status' => Objects::STATUS_ON_MODERATION,
            ])
        ) {
            return "true";
        }
    }

    public function actionUnpublish()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('object_id');
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);

        if (
            $index->updateDocuments([
                'id' => (int) $id,
                'status' => Objects::STATUS_READY_FOR_PUBLISH,
            ])
        ) {
            return "true";
        }
        return false;
    }

    public function actionAddRoom($object_id)
    {
        $id = $object_id;
        $model = new RoomCat();
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);
        $resultArr = [];

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $bedTypes = [];
            if (isset($model->bed_types) && is_array($model->bed_types)) {
                foreach ($model->bed_types as $key => $val) {
                    if ($val['checked']) {
                        $bedTypeDetails = $model->bedTypes(); // Get all bed types
                        $bedTypeTitle = isset($bedTypeDetails[$key]) ? $bedTypeDetails[$key][0] : 'Unknown';
                        $bedTypes[] = [
                            'id' => (int) $key,
                            'title' => $bedTypeTitle,
                            'quantity' => (int) $val['quantity']
                        ];
                    }
                }
            }

            $room_id = $model->id; // Use the actual model ID directly

            $meiliRooms = [];
            if (isset($object['rooms']) && is_array($object['rooms'])) {
                $meiliRooms = $object['rooms'];
            }
            $status = Objects::currentStatus($object_id, $object['status'] ? $object['status'] : Objects::STATUS_NOT_PUBLISHED);
            $default_prices = [];
            foreach ($model->default_prices as $val) {
                $default_prices[] = (float) $val;
            }

            $rooms_arr = [
                'id' => (int) $room_id,
                'room_title' => $model->typeTitle($model->type_id),
                'guest_amount' => (int) $model->guest_amount,
                'similar_room_amount' => (int) $model->similar_room_amount,
                'room_left' => (int) (int) $model->similar_room_amount,
                'area' => (int) $model->area,
                'bathroom' => (int) $model->bathroom,
                'balcony' => (int) $model->balcony,
                'air_cond' => (int) $model->air_cond,
                'kitchen' => (int) $model->kitchen,
                'base_price' => $model->default_prices ? (float) $model->default_prices[0] : 0,
                'bed_types' => $bedTypes,
                'default_prices' => $default_prices,
            ];

            $meiliRooms[] = $rooms_arr;
            $meiliRooms = array_values($meiliRooms);

            $meilisearchData = [
                'id' => (int) $id,
                'rooms' => $meiliRooms,
                'status' => $status
            ];

            if ($index->updateDocuments($meilisearchData)) {
                // Save the room data in the session to use it in actionRoom
                Yii::$app->session->set('new_room_data', $rooms_arr);

                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->redirect(['room', 'id' => $model->id, 'object_id' => $id]);
            }
        } else {
            return $this->render('rooms/create', [
                'model' => $model,
                'id' => $id,
                'object_title' => $object['name'][0],
                'object_id' => $id
            ]);
        }
    }

    public function actionRoomBeds($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $bind_model = RoomCat::findOne($id);

        if (!$bind_model) {
            throw new NotFoundHttpException('Room not found.');
        }

        $new_room_data = Yii::$app->session->get('new_room_data');
        if ($new_room_data && $new_room_data['id'] == $id) {
            $room = $new_room_data;
            Yii::$app->session->remove('new_room_data');
        } else {
            $rooms = $object['rooms'] ?? []; // Get existing rooms
            $room = null;
            foreach ($rooms as &$roomData) {
                if ($roomData['id'] == $id) {
                    $room = &$roomData;
                    break;
                }
            }

            if (!$room) {
                throw new NotFoundHttpException('Room not found in Meilisearch.');
            }
        }

        $model = new RoomCat($room);
        $model->setAttributes($room, false);
        $model->img = $bind_model->getImage() ? $bind_model->getImage()->id : null;

        // Prepopulate bed_types
        $model->bed_types = [];
        if (isset($room['bed_types']) && is_array($room['bed_types'])) {
            foreach ($room['bed_types'] as $bedType) {
                $model->bed_types[$bedType['id']] = [
                    'checked' => $bedType['quantity'] > 0 ? 1 : 0,  // Set checked if quantity > 0
                    'quantity' => $bedType['quantity'],
                ];
            }
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $bedTypes = [];
            if (isset($model->bed_types) && is_array($model->bed_types)) {
                $bedTypeDetails = $model->bedTypes(); // Only call this once

                foreach ($model->bed_types as $key => $val) {
                    if (!empty($val['checked'])) {
                        $bedTypes[] = [
                            'id' => (int) $key,
                            'title' => $bedTypeDetails[$key] ?? [],
                            'quantity' => (int) $val['quantity']
                        ];
                    }
                }
            }

            $room['bed_types'] = $bedTypes;
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->set('new_room_data', $room);
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }


        return $this->render('rooms/beds', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object['name'][0],
            'bindModel' => $bind_model,
            'room_id' => $id
        ]);
    }

    public function actionEditRoom($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $bind_model = RoomCat::findOne($id);

        if (!$bind_model) {
            throw new NotFoundHttpException('Room not found.');
        }

        $rooms = $object['rooms'] ?? []; // Get existing rooms
        $room = null;

        foreach ($rooms as &$roomData) {
            if ($roomData['id'] == $id) {
                $room = &$roomData;
                break;
            }
        }

        if (!$room) {
            throw new NotFoundHttpException('Room not found in Meilisearch.');
        }

        $model = new RoomCat($room);
        $model->setAttributes($room, false);
        $model->img = $bind_model->getImage() ? $bind_model->getImage()->id : null;

        // Prepopulate bed_types
        $model->bed_types = [];
        if (isset($room['bed_types']) && is_array($room['bed_types'])) {
            foreach ($room['bed_types'] as $bedType) {
                $model->bed_types[$bedType['id']] = [
                    'checked' => $bedType['quantity'] > 0 ? 1 : 0,  // Set checked if quantity > 0
                    'quantity' => $bedType['quantity'],
                ];
            }
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->images = UploadedFile::getInstances($model, 'images');

            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;
                    $image->saveAs($path);
                    $bind_model->attachImage($path, true);
                    @unlink($path);
                }
            }

            $bedTypes = [];
            if (isset($model->bed_types) && is_array($model->bed_types)) {
                foreach ($model->bed_types as $key => $val) {
                    if ($val['checked']) {
                        $bedTypeDetails = $model->bedTypes(); // Get all bed types
                        $bedTypeTitle = isset($bedTypeDetails[$key]) ? $bedTypeDetails[$key][0] : 'Unknown';
                        $bedTypes[] = [
                            'id' => (int) $key,
                            'title' => $bedTypeTitle,
                            'quantity' => (int) $val['quantity']
                        ];
                    }
                }
            }

            $default_prices = [];
            foreach ($model->default_prices as $val) {
                $default_prices[] = (float) $val;
            }

            // Update only the existing room data
            $room['room_title'] = $model->typeTitle($model->type_id);
            $room['guest_amount'] = (int) $model->guest_amount;
            $room['similar_room_amount'] = (int) $model->similar_room_amount;
            $room['area'] = (int) $model->area;
            $room['bathroom'] = (int) $model->bathroom;
            $room['balcony'] = (int) $model->balcony;
            $room['air_cond'] = (int) $model->air_cond;
            $room['kitchen'] = (int) $model->kitchen;
            $room['base_price'] = $model->default_prices ? (float) $model->default_prices[0] : 0;
            $room['default_prices'] = $default_prices;
            $room['img'] = $model->img;
            //$room['images'] = $bind_model->getPictures();
            $room['bed_types'] = $bedTypes; // The processed bed_types in the required format

            // Update the document in Meilisearch
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms // Preserve all rooms
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('rooms/update', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object['name'][0],
            'bindModel' => $bind_model
        ]);
    }

    public function actionDeleteRoom($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $meiliIndex = $client->index('object'); // Переименовал переменную
        $object = $meiliIndex->getDocument($object_id);
        $model = RoomCat::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Room not found.');
        }

        $rooms = $object['rooms'] ?? []; // Get existing rooms
        $roomIndex = null;

        // Найдем индекс комнаты в массиве
        foreach ($rooms as $i => $roomData) { // Изменил имя переменной с $index на $i
            if ($roomData['id'] == $id) {
                $roomIndex = $i;
                break;
            }
        }

        if ($roomIndex === null) {
            throw new NotFoundHttpException('Room not found in Meilisearch.');
        }

        // Удаляем комнату из массива
        array_splice($rooms, $roomIndex, 1);
        if (
            $client->index('object')->updateDocuments([
                'id' => (int) $object_id,
                'rooms' => $rooms
            ])
        ) { // Используем правильную переменную
            Yii::$app->session->setFlash('success', 'Комната успешно удалена!');
            return $this->redirect(['room-list', 'object_id' => $object_id]);
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении комнаты.');
            return $this->redirect(['room-list', 'object_id' => $object_id]);
        }
    }


    public function actionAddTariff($object_id)
    {
        $model = new Tariff();
        $model->object_id = $object_id;

        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $object_title = $object['name'][0];

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                $room_list = $object['rooms'] ?? [];
                try {
                    $rooms = $object['rooms'] ?? [];
                    $updatedRooms = [];

                    foreach ($rooms as $roomData) {
                        if (!is_array($roomData) || !isset($roomData['id'])) {
                            Yii::warning("Invalid room data: " . json_encode($roomData));
                            continue;
                        }

                        $roomData['id'] = (int) $roomData['id'];
                        if (in_array($roomData['id'], $rooms)) {
                            $roomData['tariff'] = $roomData['tariff'] ?? [];

                            $is_returnable = null;
                            if ($model->cancellation == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
                                $is_returnable = true;
                            } elseif ($model->cancellation == Tariff::NO_CANCELLATION) {
                                $is_returnable = false;
                            }

                            $cancellation_terms = [
                                'type' => $model->getCancellationType((int) $model->cancellation),
                                'penalty_percent' => (double) $model->penalty_sum,
                                'penalty_days' => (int) $model->penalty_days,
                                'is_returnable' => $is_returnable,
                            ];

                            $tariff = [
                                'id' => (int) $model->id,
                                'payment_on_book' => (int) $model->payment_on_book,
                                'cancellation' => $cancellation_terms,
                                'meal_type' => [
                                    'id' => (int) $model->meal_type,
                                    'name' => Objects::mealTypeFull($model->meal_type)
                                ],
                                'title' => [$model->title, $model->title_en, $model->title_ky],
                                'object_id' => (int) $object_id,
                                'price' => (float) ($roomData['base_price'] ?? 0),
                                'from_date' => '',
                                'to_date' => '',
                                'prices' => [],
                            ];

                            $roomData['tariff'][] = $tariff;
                        }

                        $updatedRooms[] = $roomData;
                    }

                    $status = Objects::currentStatus($object_id, $object['status'] ?? Objects::STATUS_NOT_PUBLISHED);

                    // ✅ Save to Meilisearch
                    $meilisearchData = [
                        'id' => (int) $object_id,
                        'rooms' => $updatedRooms,
                        'status' => $status
                    ];
                    $index->updateDocuments([$meilisearchData]);


                    Yii::$app->session->set('updated_rooms_' . $object_id, $updatedRooms);

                    return $this->redirect(['tariff-list', 'object_id' => $object_id]);

                } catch (\Exception $e) {
                    Yii::error("Meilisearch operation error: " . $e->getMessage());
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('/tariff/create', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object_title
        ]);
    }


    public function actionEditTariff($id, $object_id)
    {
        $model = Tariff::findOne($id);
        $model->object_id = $object_id;

        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $object_title = $object['name'][0];

        // Get all rooms and assign pre-checked room IDs
        $rooms = $object['rooms'] ?? [];

        $boundRoomIds = [];
        foreach ($rooms as $room) {
            if (!empty($room['tariff'])) {
                foreach ($room['tariff'] as $tariff) {
                    if ((int) $tariff['id'] === (int) $model->id) {
                        $boundRoomIds[] = (int) $room['id'];
                        break;
                    }
                }
            }
        }

        // Populate the model's room_list for checkbox pre-selection
        $model->room_list = $boundRoomIds;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                $room_list = $model->room_list;

                try {
                    $updatedRooms = [];

                    foreach ($rooms as $roomData) {
                        if (!is_array($roomData) || !isset($roomData['id'])) {
                            Yii::warning("Invalid room data: " . json_encode($roomData));
                            continue;
                        }

                        $roomData['id'] = (int) $roomData['id'];
                        $roomData['tariff'] = $roomData['tariff'] ?? [];

                        // Remove this tariff from all rooms
                        $roomData['tariff'] = array_filter($roomData['tariff'], function ($t) use ($id) {
                            return $t['id'] != $id;
                        });

                        // Re-add updated tariff only to selected rooms
                        if ($room_list && in_array($roomData['id'], $room_list)) {
                            $is_returnable = null;
                            if ($model->cancellation == Tariff::FREE_CANCELLATION_WITH_PENALTY) {
                                $is_returnable = true;
                            } elseif ($model->cancellation == Tariff::NO_CANCELLATION) {
                                $is_returnable = false;
                            }
                            $cancellation_terms = [
                                'type' => $model->getCancellationType((int) $model->cancellation),
                                'penalty_percent' => (double) $model->penalty_sum,
                                'penalty_days' => (int) $model->penalty_days,
                                'is_returnable' => $is_returnable,
                            ];
                            $tariff_data = [
                                'id' => (int) $model->id,
                                'payment_on_book' => (int) $model->payment_on_book,
                                'cancellation' => $cancellation_terms,
                                'meal_type' => ['id' => (int) $model->meal_type, 'name' => Objects::mealTypeFull($model->meal_type)],
                                'title' => [$model->title, $model->title_en, $model->title_ky],
                                'object_id' => (int) $object_id,
                                'price' => (float) $roomData['base_price'],
                                'from_date' => '',
                                'to_date' => '',
                                'prices' => [],
                            ];

                            $roomData['tariff'][] = $tariff_data;
                        }

                        $updatedRooms[] = $roomData;
                    }

                    // Update Meilisearch index
                    $meilisearchData = [
                        'id' => (int) $object_id,
                        'rooms' => $updatedRooms
                    ];

                    $index->updateDocuments([$meilisearchData]);
                    return $this->redirect(['tariff-list', 'object_id' => $object_id]);
                } catch (\Exception $e) {
                    Yii::error("Meilisearch operation error: " . $e->getMessage());
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('/tariff/update', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object_title
        ]);
    }





    function stringToNumericArray($stringInput)
    {
        // Remove square brackets and split by comma
        $cleanString = trim($stringInput, '[]');
        $items = explode(',', $cleanString);

        // Trim whitespace and convert to numeric array
        $numericArray = array_map(function ($item) {
            return (int) trim($item);
        }, $items);

        // Convert to associative array with numeric keys
        return array_values($numericArray);
    }



    public function actionTariff($object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);

        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $object_id) {
                    $room = $roomData;
                    break;
                }
            }
        }

        $model = new RoomCat($room);
        return $this->render('rooms/view', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object['name'][0]
        ]);
    }

    public function actionRemoveRoomImage($image_id, $model_id)
    {
        $post = RoomCat::findOne($model_id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $image = Image::findOne($image_id);
        if ($post->removeImage($image)) {
            return "true";
        } else {
            return "false";
        }
    }

    public function actionRemoveObjectImage()
    {
        $image_id = Yii::$app->request->post('image_id');
        $object_id = Yii::$app->request->post('object_id');

        $post = Objects::findOne($object_id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $images = $post->getImages();
        $deleted = 'false';

        foreach ($images as $image) {
            if ($image->id == $image_id) { // Replace with the actual image ID
                $post->removeImage($image);
                $deleted = 'true';
                break; // Stop after deleting the image
            }
        }
        return $deleted;
    }

    public function actionRemoveFile()
    {
        $folder = Yii::$app->request->post('folder');
        $name = Yii::$app->request->post('name');
        $id = Yii::$app->request->post('object_id');

        $path = Yii::getAlias("@webroot/uploads/documents/{$id}/{$folder}") . "/" . $name;
        if (FileHelper::unlink($path)) {
            return 'true';
        }
        return false;
    }



    public function actionPayment($object_id)
    {
        $id = $object_id;
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Fetch object from Meilisearch
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $meiliData = $searchResult[0];

        // Use session override if available
        $session = Yii::$app->session;
        $sessionKey = "object_{$id}_payment_override";
        $sessionPayment = $session->get($sessionKey);

        if ($sessionPayment !== null) {
            $meiliData['payment'] = $sessionPayment;
        }

        $model = new Objects($meiliData);

        // Get available payment types from the DB
        $paymentTypes = PaymentType::find()->asArray()->all();

        // Get current selections
        $selectedPayments = $model->payment ?? [];

        if (Yii::$app->request->isPost) {
            $selectedIds = Yii::$app->request->post('payment_type', []);

            // Convert selected IDs to full associative array
            $payment_arr = [];
            foreach ($paymentTypes as $payment) {
                if (in_array($payment['id'], $selectedIds)) {
                    $payment_arr[$payment['id']] = $payment['title'];
                }
            }

            // Save to Meilisearch
            $meilisearchData = [
                'id' => $id,
                'payment' => $payment_arr
            ];

            if ($index->updateDocuments($meilisearchData)) {
                // ✅ Save override in session
                $session->set($sessionKey, $payment_arr);

                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('payment_type', [
            'model' => $model,
            'id' => $id,
            'paymentTypes' => $paymentTypes,
            'selectedPayments' => array_keys($selectedPayments)
        ]);
    }


    public function actionRoom($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $bind_model = RoomCat::findOne($id);

        if (!$bind_model) {
            throw new NotFoundHttpException('Room not found.');
        }

        $rooms = $object['rooms'] ?? []; // Get existing rooms
        $room = null;

        // Check if this is a new room redirect
        $new_room_data = Yii::$app->session->get('new_room_data');
        if ($new_room_data && $new_room_data['id'] == $id) {
            // Use the data from the session
            $room = $new_room_data;
            // Clear the session data to avoid reusing it
            Yii::$app->session->remove('new_room_data');
        } else {
            // Find the room in the Meilisearch data
            foreach ($rooms as &$roomData) {
                if ($roomData['id'] == $id) {
                    $room = &$roomData;
                    break;
                }
            }

            if (!$room) {
                // If room still not found, try to build it from the database model
                $room = [
                    'id' => (int) $bind_model->id,
                    'room_title' => $bind_model->typeTitle($bind_model->type_id),
                    'guest_amount' => (int) $bind_model->guest_amount,
                    'similar_room_amount' => (int) $bind_model->similar_room_amount,
                    'area' => (int) $bind_model->area,
                    'bathroom' => (int) $bind_model->bathroom,
                    'balcony' => (int) $bind_model->balcony,
                    'air_cond' => (int) $bind_model->air_cond,
                    'kitchen' => (int) $bind_model->kitchen,
                    'base_price' => (int) $bind_model->base_price,
                    'bed_types' => [] // Empty array as we don't have this data
                ];
            }
        }

        $model = new RoomCat($room);
        $model->setAttributes($room, false);
        $model->img = $bind_model->getImage() ? $bind_model->getImage()->id : null;


        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;
                    $image->saveAs($path);
                    $bind_model->attachImage($path, true);
                    @unlink($path);
                }
            }

            $default_prices = [];
            foreach ($model->default_prices as $val) {
                $default_prices[] = (float) $val;
            }



            $room['room_title'] = $model->typeTitle($model->type_id);
            $room['guest_amount'] = (int) $model->guest_amount;
            $room['similar_room_amount'] = (int) $model->similar_room_amount;
            $room['area'] = (int) $model->area;
            $room['bathroom'] = (int) $model->bathroom;
            $room['balcony'] = (int) $model->balcony;
            $room['air_cond'] = (int) $model->air_cond;
            $room['kitchen'] = (int) $model->kitchen;
            $room['base_price'] = (int) $model->base_price;
            $room['img'] = $model->img;
            $room['default_prices'] = $default_prices;


            // Update the room in the rooms array if it exists
            $roomUpdated = false;
            foreach ($rooms as &$roomData) {
                if ($roomData['id'] == $id) {
                    $roomData = $room;
                    $roomUpdated = true;
                    break;
                }
            }

            // If the room wasn't in the array, add it
            if (!$roomUpdated) {
                $rooms[] = $room;
            }

            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms // Updated rooms array

            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->set('new_room_data', $room);
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('rooms/update', [
            'model' => $model,
            'object_id' => $object_id,
            'room_id' => $id,
            'object_title' => $object['name'][0],
            'bindModel' => $bind_model
        ]);
    }

    public function actionPictures($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);
        $model = RoomCat::findOne($id);

        $rooms = $object['rooms'] ?? []; // Get existing rooms
        $room = null;

        foreach ($rooms as &$roomData) { // Use reference to modify the array
            if ($roomData['id'] == $id) {
                $room = &$roomData;
                break;
            }
        }

        if (!$room) {
            throw new NotFoundHttpException('Room not found in Meilisearch.');
        }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->images = UploadedFile::getInstances($model, 'images');
            $mainTempId = $model->main_img; // e.g., 'new_filename_xyz' or existing ID
            $mainImageSet = false;

            if ($model->images) {
                foreach ($model->images as $image) {
                    // Match the frontend temp ID logic
                    $baseNameSanitized = preg_replace('/\W+/', '_', $image->name);
                    $possibleTempIdPrefix = 'new_' . $baseNameSanitized;

                    $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;

                    if ($image->saveAs($path)) {
                        // Match full frontend-generated ID prefix
                        $isMain = false;
                        if (!$mainImageSet && strpos($mainTempId, $possibleTempIdPrefix) === 0) {
                            $isMain = true;
                            $mainImageSet = true;
                        }

                        $model->attachImage($path, $isMain);
                        @unlink($path);
                    }
                }
            }

            if ($model->main_img) {
                $image_id = $model->main_img;
                foreach ($model->getImages() as $image) {
                    if ($image->id == $image_id) {
                        $model->setMainImage($image);
                    }
                }
            }

            $room['images'] = $model->getPictures();

            // Update the document in Meilisearch
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms // Preserve all rooms
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }
        return $this->render('rooms/pictures', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object['name'][0],
            'room_id' => $id,
            'title' => $room['room_title'],
            'picture_list' => $model->getImages()
        ]);
    }


    public function actionRoomComfort($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $comforts_post = Yii::$app->request->post('comforts');

        $object = $index->getDocument($object_id);
        $room = [];
        $model = new Objects($object);

        // 🔁 Load the correct room from Meilisearch
        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $id) {
                    $room = $roomData;
                    break;
                }
            }
        }

        // 🔄 Use updated room data from session if exists
        $sessionKey = 'updated_room_' . $id;
        if (Yii::$app->session->has($sessionKey)) {
            $room = Yii::$app->session->get($sessionKey);
            Yii::$app->session->remove($sessionKey);
        }

        // ✅ Process form submission
        if (!empty($comforts_post)) {
            $comfort_ids = [];

            // 🧠 Extract selected comfort IDs
            foreach ($comforts_post as $cat => $comfortsInCat) {
                foreach ($comfortsInCat as $comfortId => $data) {
                    if (isset($data['selected'])) {
                        $comfort_ids[] = $comfortId;
                    }
                }
            }

            // 🗃 Fetch comforts from DB
            $comfort_models = RoomComfort::find()->where(['id' => $comfort_ids])->all();
            $comfortArr = [];

            foreach ($comfort_models as $item) {
                $catId = $item->category_id;
                $itemId = $item->id;

                // 🏷 Check if this comfort was selected
                if (isset($comforts_post[$catId][$itemId]['selected'])) {
                    $isPaid = isset($comforts_post[$catId][$itemId]['is_paid']) ? 1 : 0;

                    $comfortArr[$catId][$itemId] = [
                        'ru' => $item->title,
                        'en' => $item->title_en,
                        'ky' => $item->title_ky,
                        'is_paid' => $isPaid,
                    ];
                }
            }

            // 🔄 Update the correct room in Meilisearch object
            foreach ($object['rooms'] as $i => $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $id) {
                    $object['rooms'][$i]['comfort'] = $comfortArr;
                    $room = $object['rooms'][$i];
                    break;
                }
            }

            // 💾 Send update to Meilisearch
            $index->updateDocuments([
                [
                    'id' => (int) $object_id,
                    'rooms' => $object['rooms']
                ]
            ]);

            // 💡 Save updated room to session for immediate feedback
            Yii::$app->session->set($sessionKey, $room);
            Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');

            return $this->refresh();
        }

        return $this->render('rooms/comfort', [
            'object_id' => $object_id,
            'object_title' => $object['name'][0],
            'room_id' => $id,
            'room' => $room,
            'model' => $model,
        ]);
    }


    /**
     * Deletes an existing Event model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);
        $client->index('object')->deleteDocument($id);


        return $this->redirect(['index']);
    }

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Objects::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionFileUpload($id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = Objects::findOne($id);
        if ($model === null) {
            return ['error' => 'Object not found'];
        }

        $model->images = UploadedFile::getInstances($model, 'images');

        if ($model->images) {
            foreach ($model->images as $image) {
                $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                $image->saveAs($path);
                $model->attachImage($path, true); // ✅ save image to model
                @unlink($path); // clean up temp file
            }
            return ['filename' => $image->name]; // last uploaded file name
        }

        return ['error' => 'No file uploaded'];
    }

    public function actionFinances($object_id)
    {
        $searchModel = new BookingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['object_id' => $object_id]);
        $totalIncome = clone $dataProvider->query;
        $income = $totalIncome->sum('sum');
        $user_fee = Yii::$app->user->identity->fee_percent ? Yii::$app->user->identity->fee_percent : User::FIXED_FEE;
        $comission = $income / 100 * $user_fee;
        $payment = $income - $comission;
        //$comission = $income - $percent;
        $active = "all_active";

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
        return $this->render('finances', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'object_id' => $object_id,
            'active' => $active,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'date_from_string' => $date_from_string,
            'date_to_string' => $date_to_string,
            'amount' => $dataProvider->getTotalCount(),
            'income' => $income,
            'comission' => $comission,
            'payment' => $payment

        ]);
    }


}


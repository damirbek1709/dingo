<?php

namespace app\modules\owner\controllers;

use app\models\RoomComfort;
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
                    'actions' => ['room-list', 'room', 'view', 'delete', 'comfort', 'index-admin'],
                    'roles' => ['admin'],
                ],
                [
                    'allow' => true,
                    'actions' => ['room-list', 'edit-room', 'add-room', 'delete'],
                    'roles' => ['admin'], // Authenticated users
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
                    'actions' => ['add-room'],
                    'roles' => ['admin'], // Authenticated users
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('id');
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
                    'actions' => ['view', 'comfort', 'payment', 'terms', 'room-list', 'add-room', 'update', 'edit-room', 'delete'],
                    'roles' => ['owner'],
                    'matchCallback' => function () {
                        $object_id = Yii::$app->request->get('id');
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
                    'actions' => ['room', 'edit-room', 'add-tariff', 'tariff-list', 'room-comfort'],
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
                    'actions' => ['index', 'create', 'add-tariff', 'prices'],
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

    public function actionRoomList($id)
    {
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
            'object_id' => $id,
            'object_title' => $object['name'][0],
        ]);
    }

    public function actionTariffList($object_id, $room_id)
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
        ]);
    }

    public function actionPrices($id)
    {
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


    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        //$this->layout = 'main';
        $client = Yii::$app->meili->connect();
        $index = $client->index('object'); // Replace with your actual Meilisearch index

        // Fetch record from Meilisearch
        $searchResult = $index->getDocument($id);

        if (empty($searchResult)) {
            throw new NotFoundHttpException('Record not found.');
        }

        // Convert the result into a Yii2 model
        $model = new Objects($searchResult);
        $bind_model = Objects::find()->where(['id' => $id])->one();


        return $this->render('view', [
            'model' => $model,
            'bind_model' => $bind_model
        ]);
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

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
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
                }
                $object_arr = [
                    'id' => $model->id,
                    'name' => array_values([$model->name, $model->name_en, $model->name_ky]),
                    'type' => (int) $model->type,
                    'reception' => (int) $model->reception,
                    'city' => [$model->city, $model->city_en, $model->city_ky],
                    'address' => [$model->address, $model->address_en, $model->address_ky],
                    'description' => [$model->description, $model->description_en, $model->description_ky],
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
                ];

                $index->addDocuments($object_arr);
                return $this->redirect(['view', 'id' => $model->id]);
            }

        }

        return $this->render('create', [
            'model' => $model
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
    public function actionUpdate($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Fetch record from Meilisearch
        $searchResult = $index->getDocument($id);

        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        // Convert the first result into a model
        $model = new Objects($searchResult);
        $bind_model = Objects::findOne($id);

        // Handle form submission
        if ($model->load(Yii::$app->request->post())) {
            $model->type = (int) $model->type;
            $model->lat = (float) $model->lat;
            $model->lon = (float) $model->lon;
            $model->user_id = (int) Yii::$app->user->id;
            $bind_model->link_id = $id;

            $request = Yii::$app->request->post();

            $model->name = [
                $request['Objects']['name'] ?? '',
                $request['Objects']['name_en'] ?? '',
                $request['Objects']['name_ky'] ?? '',
            ];

            $model->city = [
                $request['Objects']['city'] ?? '',
                $request['Objects']['city_en'] ?? '',
                $request['Objects']['city_ky'] ?? '',
            ];

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


            if ($bind_model->save(false)) {
                $bind_model->images = UploadedFile::getInstances($model, 'images');
                if ($model->images) {
                    foreach ($bind_model->images as $image) {
                        $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                        $image->saveAs($path);
                        $bind_model->attachImage($path, true);
                        @unlink($path);
                    }
                }

                $object_arr = [
                    'id' => (int) $model->id,
                    'name' => $model->name,
                    'type' => (int) $model->type,
                    'reception' => (int) $model->reception,
                    'city' => $model->city,
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
                    'user_id' => (int) Yii::$app->user->id,
                    'email' => $model->email,
                    'features' => $model->features ?? [],
                    'images' => $model->getPictures(),
                ];



                $index->updateDocuments($object_arr);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'id' => $id,
            'bindModel' => $bind_model
        ]);
    }

    public function actionComfort($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $comfort_list = Yii::$app->request->post('comforts');

        $searchResult = $index->getDocument($id);
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $model = new Objects($searchResult);

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

            $meilisearchData = [
                'id' => (int) $id,
                'comfort_list' => $comfortArr
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('comfort', [
            'model' => $model,
            'id' => $id,
        ]);
    }


    public function actionTerms($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Fetch object from Meilisearch
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        // Load object data
        $model = new Objects($searchResult[0]);
        $data = $searchResult[0];

        // Assign saved values to model attributes
        $model->early_check_in = $data['terms']['early_check_in'] ?? false;
        $model->late_check_in = $data['terms']['late_check_in'] ?? false;
        $model->internet_public = $data['terms']['internet_public'] ?? false;
        $model->animals_allowed = $data['terms']['animals_allowed'] ?? false;
        $model->meal_purchaise = $data['terms']['meal_purchaise'] ?? false;
        $model->meal_terms = $data['terms']['meal_terms'] ?? [];
        $model->children = $data['terms']['children'] ?? [];


        if (Yii::$app->request->isPost) {
            // Save form data
            $model->early_check_in = Yii::$app->request->post('early_check_in', 0);
            $model->late_check_in = Yii::$app->request->post('late_check_in', 0);
            $model->internet_public = Yii::$app->request->post('internet_public', 0);
            $model->animals_allowed = Yii::$app->request->post('animals_allowed', 0);
            $model->meal_terms = Yii::$app->request->post('meal_terms', []);
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
                    'meal_terms' => array_values($model->meal_terms),
                    'meal_purchaise' => (bool) $model->meal_purchaise,
                    'children' => (int) $model->children,
                ]
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('terms', [
            'model' => $model,
            'id' => $id,
        ]);
    }



    public function actionAddRoom($id)
    {
        $model = new RoomCat();
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }

            $img = "";
            if ($model->img) {
                $img = $model->getImageById($model->img);
            }

            $room_id = RoomCat::find()->orderBy(['id' => SORT_DESC])->one()->id;
            $meiliRooms = [];

            if (isset($object['rooms']) && is_array($object['rooms'])) {
                $meiliRooms = $object['rooms'];
                $room_id = $room_id + 1;
            }

            $rooms_arr = [
                'id' => (int) $room_id,
                'room_title' => $model->typeTitle($model->type_id),
                'guest_amount' => (int) $model->guest_amount,
                'similar_room_amount' => (int) $model->similar_room_amount,
                'area' => (int) $model->area,
                'bathroom' => (int) $model->bathroom,
                'balcony' => (int) $model->balcony,
                'air_cond' => (int) $model->air_cond,
                'kitchen' => (int) $model->kitchen,
                'base_price' => (int) $model->base_price,
                'img' => $img,
                'images' => $model->getPictures(),
            ];
            $meiliRooms[] = $rooms_arr;
            $meiliRooms = array_values($meiliRooms);

            $meilisearchData = [
                'id' => (int) $id,
                'rooms' => $meiliRooms
            ];
            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                $model->save();
                return $this->redirect(['room', 'id' => $room_id, 'object_id' => $id]);
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

        foreach ($rooms as &$roomData) { // Use reference to modify the array
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

            // Update only the existing room data
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
            $room['images'] = $bind_model->getPictures();

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
                $room_list = $this->request->post()['Tariff']['room_list'][0];
                $room_list = $this->stringToNumericArray($room_list);

                try {
                    $rooms = $object['rooms'] ?? [];
                    $updatedRooms = [];
                    foreach ($rooms as $roomData) {
                        if (!is_array($roomData) || !isset($roomData['id'])) {
                            Yii::warning("Invalid room data: " . json_encode($roomData));
                            continue;
                        }

                        $roomData['id'] = (int) $roomData['id'];
                        if (in_array($roomData['id'], $room_list)) {
                            $roomData['tariff'] = $roomData['tariff'] ?? [];
                            $tariff = [
                                'id' => (int) $model->id,
                                'payment_on_book' => (int) $model->payment_on_book,
                                'cancellation' => (int) $model->cancellation,
                                'meal_type' => (int) $model->meal_type,
                                'title' => $model->title,
                                'object_id' => (int) $object_id,
                                'price' => (float) $roomData['base_price'],
                                'from_date' => '',
                                'to_date' => '',
                                'penalty_sum' => $model->penalty_sum,
                                'penalty_days' => $model->penalty_days,
                                'prices' => [],
                            ];

                            $roomData['tariff'][] = $tariff;
                        }
                        $updatedRooms[] = $roomData;
                    }

                    $meilisearchData = [
                        'id' => (int) $object_id,
                        'rooms' => $updatedRooms
                    ];

                    $index->updateDocuments([$meilisearchData]);
                    return $this->redirect(['room-list', 'id' => $object_id]);
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
        $images = $post->getImages();

        foreach ($images as $image) {
            if ($image->id == $image_id) { // Replace with the actual image ID
                $post->removeImage($image);
                break; // Stop after deleting the image
            }
        }
    }



    public function actionPayment($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');

        // Fetch object from Meilisearch
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $model = new Objects($searchResult[0]);

        // Get available payment types from the database
        $paymentTypes = PaymentType::find()->asArray()->all(); // ['id' => 1, 'name' => 'Visa']

        // Convert saved data into an array with IDs as keys
        $selectedPayments = $model->payment ?? [];

        if (Yii::$app->request->isPost) {
            $selectedIds = Yii::$app->request->post('payment_type', []);

            // Convert IDs to associative array {id: name}
            $payment_arr = [];
            foreach ($paymentTypes as $payment) {
                if (in_array($payment['id'], $selectedIds)) {
                    $payment_arr[$payment['id']] = $payment['title'];
                }
            }

            $meilisearchData = [
                'id' => $id,
                'payment' => $payment_arr
            ];

            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('payment_type', [
            'model' => $model,
            'id' => $id,
            'paymentTypes' => $paymentTypes,
            'selectedPayments' => array_keys($selectedPayments) // Keep only IDs
        ]);
    }

    public function actionRoom($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($object_id);

        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $id) {
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


    public function actionRoomComfort($id, $object_id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $comfort_list = Yii::$app->request->post('comforts');
        $object = $index->getDocument($object_id);

        $room = [];
        $model = new Objects($object);

        if (!empty($comfort_list)) {
            // Fetch comfort info from DB
            $comfort_models = RoomComfort::find()->where(['id' => $comfort_list])->all();
            $comfortArr = [];
            foreach ($comfort_models as $item) {
                $comfortArr[$item->category_id][$item->id] = [
                    'ru' => $item->title,
                    'en' => $item->title_en,
                    'ky' => $item->title_ky
                ];
            }

            

            // Update room inside object
            if (isset($object['rooms']) && is_array($object['rooms'])) {
                foreach ($object['rooms'] as $roomData) {
                    if (isset($roomData['id']) && $roomData['id'] == $id) {
                        $room = $roomData;
                        break;
                    }
                }
            
                // ✅ Ensure comfort is always set
                if (!isset($room['comfort'])) {
                    $room['comfort'] = [];
                }

                // Re-index the entire object with updated rooms
                $meilisearchData = [
                    'id' => (int) $object_id,
                    'rooms' => $object['rooms']
                ];

                $index->updateDocuments([$meilisearchData]); // Must pass array of documents

                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        echo "<pre>";print_r($room);echo "</pre>";die();

        return $this->render('rooms/comfort', [
            'object_id' => $object_id,
            'room_id'=>$id,
            'room' => $room,
            'model' => $model
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
}


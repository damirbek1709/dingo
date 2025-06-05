<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\BusinessAccountBridge;
use app\models\BusinessAccountBridgeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use app\models\Objects;
use yii\web\Response;
use app\models\TariffSearch;
use app\models\RoomCat;
use app\models\Comfort;
use app\models\PaymentType;
use app\models\Tariff;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use rico\yii2images\models\Image;

/**
 * BusinessAccountBridgeController implements the CRUD actions for BusinessAccountBridge model.
 */
class ObjectController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index',
                        'view',
                        'update',
                        'create',
                        'bind-tariff',
                        'bind-room',
                        'send-to-moderation',
                        'room-list',
                        'tariff-list',
                        'room',
                        'room-beds',
                        'booking',
                        'comfort',
                        'payment',
                        'terms',
                        'pictures',
                        'room-comfort',
                        'edit-tariff'
                    ],
                    'roles' => ['admin'],
                ],
            ]
        ];

        return $behaviors;
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
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
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


    public function actionIndex()
    {
        $statusFilter = Yii::$app->request->get('status');
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search('', [
            'limit' => 10000
        ]);

        $hits = $res->getHits();

        // Manual filtering
        if (!empty($statusFilter)) {
            $hits = array_filter($hits, function ($hit) use ($statusFilter) {
                return $hit['status'] == $statusFilter;
            });
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $hits,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['name', 'type', 'address', 'email', 'phone', 'status'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
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

    function translateOblast($oblast)
    {
        switch ($oblast) {
            case "Бишкек":
                return ["Bishkek", "Бишкек"];
                break;
            case "Ошская область":
                return ["Osh oblast", "Ош облусу"];
                break;
            case "Чуйская область":
                return ["Chui oblast", "Чүй облусу"];
                break;
            case "Джалал-Абадская область":
                return ["Jalal-Abad oblast", "Жалал-Абад облусу"];
                break;
            case "Нарынская область":
                return ["Naryn oblast", "Нарын облусу"];
                break;
            case "Таласская область":
                return ["Talas oblast", "Талас облусу"];
                break;
            case "Иссык-Кульская область":
                return ["Yssyk-Kul oblast", "Ысык-Көл облусу"];
                break;
            case "Баткенская область":
                return ["Batken oblast", "Баткен облусу"];
                break;
            default:
                return ["", ""];

        }
    }

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
                if ($model->images) {
                    foreach ($bind_model->images as $image) {
                        $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                        $image->saveAs($path);
                        $bind_model->attachImage($path, true);
                        @unlink($path);
                    }
                }
                if ($model->img) {
                    $main_img = Image::find()->where(['id' => $model->img])->one();
                    $bind_model->setMainImage($main_img);
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
                ];

                if (!Yii::$app->user->can('admin')) {
                    $object_arr['user_id'] = (int) Yii::$app->user->id;
                }


                $index->updateDocuments($object_arr);
                return $this->redirect(['view', 'object_id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'id' => $object_id,
            'bindModel' => $bind_model
        ]);
    }


    public function actionView($object_id)
    {
        //$this->layout = 'main';
        $client = Yii::$app->meili->connect();
        $index = $client->index('object'); // Replace with your actual Meilisearch index

        // Fetch record from Meilisearch
        $searchResult = $index->getDocument($object_id);

        if (empty($searchResult)) {
            throw new NotFoundHttpException('Record not found.');
        }

        // Convert the result into a Yii2 model
        $model = new Objects($searchResult);
        $bind_model = Objects::find()->where(['id' => $object_id])->one();


        return $this->render('view', [
            'model' => $model,
            'bind_model' => $bind_model,
            'object_id' => $object_id
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
            $room['bed_types'] = $bedTypes;


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

    public function actionSendToModeration()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('object_id');
        $status = Yii::$app->request->post('status');
        $reason = Yii::$app->request->post('message');
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);

        if (
            $index->updateDocuments([
                'id' => (int) $id,
                'status' => (int) $status,
                'deny_reason' => $reason
            ])
        ) {
            return Objects::statusData($status);
        }
        return false;
    }


}

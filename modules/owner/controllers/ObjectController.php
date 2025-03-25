<?php

namespace app\modules\owner\controllers;

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
                    'actions' => ['room-list', 'room', 'view'],
                    'roles' => ['admin'],
                ],
                [
                    'allow' => true,
                    'actions' => ['room-list', 'edit-room', 'add-room'],
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
                    'actions' => ['view', 'comfort', 'payment', 'terms', 'room-list', 'add-room', 'update', 'edit-room'],
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
                    'actions' => ['room', 'edit-room'],
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
                    'actions' => ['index', 'create'],
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
            'object_title' => $object['name'],
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
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();

        if (empty($searchResult)) {
            throw new NotFoundHttpException('Record not found.');
        }

        // Convert the result into a Yii2 model
        $model = new Objects($searchResult[0]);

        return $this->render('view', [
            'model' => $model,
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

        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->id = (int) $this->lastIncrement() + 1;
            $model->lat = (float) $model->lat;
            $model->lon = (float) $model->lon;
            $model->user_id = (int) Yii::$app->user->id;
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }

            $index->addDocuments(array_values([$model->attributes]));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
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
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();

        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        // Convert the first result into a model
        $model = new Objects($searchResult[0]);


        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }

            $index->updateDocuments([$model->attributes]);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'id' => $id,
        ]);
    }

    public function actionComfort($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $comfort_list = Yii::$app->request->post('comforts');
        $searchResult = $client->index('object')->getDocument($id);
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $model = new Objects($searchResult);

        if (isset($comfort_list)) {
            $comfort_models = Comfort::find()->where(['id' => $comfort_list])->all();
            $comfortArr = [];
            foreach ($comfort_models as $item) {
                $comfortArr[$item->category_id][$item->id] = ['ru' => $item->title, 'en' => $item->title_en, 'ky' => $item->title_ky];
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
        $object = $client->index('object')->getDocument($id);
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
                $last_room = end($object['rooms']);
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
            $meiliRooms[] = $rooms_arr;  // Append new room data

            // Ensure it's a clean, sequential array (remove any potential associative keys)
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
                'object_title' => $object['name'],
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

        $room = null;
        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $id) {
                    $room = $roomData;
                    break;
                }
            }
        }
        $model = new RoomCat();
        if ($room) {
            $model->setAttributes($room, false);
        }
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
            $rooms_arr = [
                'id' => (int) $id,
                'room_title' => $model->typeTitle($model->type_id),
                'guest_amount' => (int) $model->guest_amount,
                'similar_room_amount' => (int) $model->similar_room_amount,
                'area' => (int) $model->area,
                'bathroom' => (int) $model->bathroom,
                'balcony' => (int) $model->balcony,
                'air_cond' => (int) $model->air_cond,
                'kitchen' => (int) $model->kitchen,
                'base_price' => (int) $model->base_price,
                'img' => $model->img,
                'images' => $bind_model->getPictures(),
            ];

            $meiliRooms = [$rooms_arr];
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $meiliRooms
            ];
            if ($index->updateDocuments($meilisearchData)) {
                Yii::$app->session->setFlash('success', 'Ваши изменения сохранены!');
                return $this->refresh();
            }
        }

        return $this->render('rooms/update', [
            'model' => $model,
            'object_id' => $object_id,
            'object_title' => $object['name'],
            'bindModel' => $bind_model
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
            'object_title' => $object['name']
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



    /**
     * Deletes an existing Event model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

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

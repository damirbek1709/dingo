<?php

namespace app\modules\owner\controllers;
use Yii;
use app\models\Tariff;
use app\models\TariffSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\filters\AccessControl;

/**
 * TariffController implements the CRUD actions for Tariff model.
 */
class TariffController extends Controller
{
    public $layout = "main";
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'update', 'create', 'bind-tariff','bind-room','edit-tariff'],
                    'roles' => ['owner'],
                ],

                [
                    'allow' => true,
                    'actions' => ['delete'],
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
            ]
        ];

        return $behaviors;
    }

    /**
     * Lists all Tariff models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TariffSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tariff model.
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

    /**
     * Creates a new Tariff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($object_id)
    {
        $model = new Tariff();

        if ($this->request->isPost) {
            $model->object_id = $object_id;
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'object_id' => $object_id,
        ]);
    }

    /**
     * Updates an existing Tariff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $object_id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'object_id' => $object_id,
        ]);
    }

    /**
     * Deletes an existing Tariff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $object_id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/owner/object/tariff-list','object_id'=>$object_id]);
    }

    public function actionEditTariff()
    {
        $tariff_list = Yii::$app->request->post('tariff_list');
        $object_id = Yii::$app->request->post('object_id');
        $room_id = Yii::$app->request->post('room_id');

        Yii::$app->response->format = Response::FORMAT_JSON;

        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);

        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $index => $roomData) {
                if (isset($roomData['id']) && $roomData['id'] == $room_id) {
                    $object['rooms'][$index]['tariff'] = array_values($tariff_list); // Replace tariffs
                    break;
                }
            }

            // ✅ Save updated object
            $client->index('object')->updateDocuments([
                'id' => $object_id,
                'rooms' => $object['rooms']
            ]);
        }

        return $tariff_list;
    }


    /**
     * Finds the Tariff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Tariff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tariff::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionBindTariff()
    {
        if (Yii::$app->request->isAjax) {
            $tariffId = Yii::$app->request->post('tariff_id');
            $checked = Yii::$app->request->post('checked'); // 1 if checked, 0 if unchecked
            $room_id = Yii::$app->request->post('room_id');
            $object_id = Yii::$app->request->post('object_id');

            $response = ['success' => false];
            $tariff = Tariff::findOne($tariffId);
            $client = Yii::$app->meili->connect();
            $object = $client->index('object')->getDocument($object_id);

            $rooms = $object['rooms'] ?? []; // Get existing rooms
            $room = null;

            // Find the room with the matching ID
            foreach ($rooms as &$roomData) {
                if ($roomData['id'] == $room_id) {
                    $room = &$roomData;
                    break;
                }
            }

            if (!$room) {
                throw new NotFoundHttpException('Room not found in Meilisearch.');
            }

            if ($checked == 'true') {
                // When checkbox is checked, add the tariff
                $push_arr = [
                    "id" => $tariffId,
                    "title" => $tariff->title,
                    "payment_on_book" => $tariff->payment_on_book ? 1 : 0,
                    "payment_on_reception" => $tariff->payment_on_reception ? 1 : 0,
                    "cancellation" => (int) $tariff->cancellation,
                    "meal_type" => (int) $tariff->meal_type,
                    "object_id" => (int) $object_id,
                    "prices" => [] // Prices can be added here as necessary
                ];
                // Add the new tariff to the room's tariffs array
                $room['tariff'][] = $push_arr;
            } else {
                // When checkbox is unchecked, remove the tariff
                if (isset($room['tariff']) && is_array($room['tariff'])) {
                    // Filter the tariffs and remove the one with the given tariffId
                    $room['tariff'] = array_filter($room['tariff'], function ($tariffItem) use ($tariffId) {
                        return $tariffItem['id'] != $tariffId;
                    });
                    // Re-index the array to fix the keys after filtering
                    $room['tariff'] = array_values($room['tariff']);
                }
            }

            // Prepare the data to update in Meilisearch
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms // Update rooms with modified tariff data
            ];

            // Update Meilisearch document
            if ($client->index('object')->updateDocuments($meilisearchData)) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true];
            }
        }

        return ['success' => false];
    }

    public function actionBindRoom()
    {
        if (Yii::$app->request->isAjax) {
            $tariffId = Yii::$app->request->post('tariff_id');
            $checked = Yii::$app->request->post('checked'); // 1 if checked, 0 if unchecked
            $room_id = Yii::$app->request->post('room_id');
            $object_id = Yii::$app->request->post('object_id');

            $response = ['success' => false];
            $tariff = Tariff::findOne($tariffId);
            $client = Yii::$app->meili->connect();
            $object = $client->index('object')->getDocument($object_id);

            $rooms = $object['rooms'] ?? []; // Get existing rooms
            $room = null;

            // Find the room with the matching ID
            foreach ($rooms as &$roomData) {
                if ($roomData['id'] == $room_id) {
                    $room = &$roomData;
                    break;
                }
            }

            if (!$room) {
                throw new NotFoundHttpException('Room not found in Meilisearch.');
            }

            if ($checked == 'true') {
                // When checkbox is checked, add the tariff
                $push_arr = [
                    "id" => $tariffId,
                    "title" => $tariff->title,
                    "payment_on_book" => $tariff->payment_on_book ? 1 : 0,
                    "payment_on_reception" => $tariff->payment_on_reception ? 1 : 0,
                    "cancellation" => (int) $tariff->cancellation,
                    "meal_type" => (int) $tariff->meal_type,
                    "object_id" => (int) $object_id,
                    "prices" => [] // Prices can be added here as necessary
                ];
                // Add the new tariff to the room's tariffs array
                $room['tariff'][] = $push_arr;
            } else {
                // When checkbox is unchecked, remove the tariff
                if (isset($room['tariff']) && is_array($room['tariff'])) {
                    // Filter the tariffs and remove the one with the given tariffId
                    $room['tariff'] = array_filter($room['tariff'], function ($tariffItem) use ($tariffId) {
                        return $tariffItem['id'] != $tariffId;
                    });
                    // Re-index the array to fix the keys after filtering
                    $room['tariff'] = array_values($room['tariff']);
                }
            }

            // Prepare the data to update in Meilisearch
            $meilisearchData = [
                'id' => (int) $object_id,
                'rooms' => $rooms // Update rooms with modified tariff data
            ];

            // Update Meilisearch document
            if ($client->index('object')->updateDocuments($meilisearchData)) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true];
            }
        }

        return ['success' => false];
    }


}

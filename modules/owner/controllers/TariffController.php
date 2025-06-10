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
use app\models\Objects;
use DateTime;
use Exception;

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
                    'actions' => ['index', 'view', 'update', 'create', 'bind-tariff', 'bind-room', 'edit-tariff'],
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
        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);

        if (isset($object['rooms']) && is_array($object['rooms'])) {
            foreach ($object['rooms'] as $index => $roomData) {
                if (isset($roomData['tariff'])) {
                    foreach ($roomData['tariff'] as $tariff_item) {
                        if ($tariff_item['id'] == $id) {
                            array_splice($roomData['tariff'], $tariff_item['id'], 1);
                            break;
                        }
                    }
                }
            }
        }
        $client->index('object')->updateDocuments([
            'id' => $object_id,
            'rooms' => $object['rooms']
        ]);

        return $this->redirect(['/owner/object/tariff-list', 'object_id' => $object_id]);
    }

    public function actionEditTariff()
    {
        $tariff_list = Yii::$app->request->post('tariff_list');
        $object_id = Yii::$app->request->post('object_id');
        $room_id = Yii::$app->request->post('room_id');
        $room_left = Yii::$app->request->post('room_left');

        Yii::$app->response->format = Response::FORMAT_JSON;

        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);

        if (!is_array($tariff_list) || !is_array($object['rooms'])) {
            return ['error' => 'Invalid data structure'];
        }

        foreach ($object['rooms'] as $roomIndex => $roomData) {
            if ((int) $roomData['id'] !== (int) $room_id) {
                continue;
            }
            $object['rooms'][$roomIndex]['room_left'] = (int) $room_left;



            $existingTariffs = $roomData['tariff'] ?? [];

            foreach ($tariff_list as $newTariffId => $newTariffData) {
                $newTariffId = (int) $newTariffId;
                $found = false;

                foreach ($existingTariffs as &$existingTariff) {
                    if ((int) $existingTariff['id'] === $newTariffId) {
                        $existingTariff['prices'] = $existingTariff['prices'] ?? [];
                        $existingPrices = $existingTariff['prices'];

                        if (isset($newTariffData['prices']) && is_array($newTariffData['prices'])) {
                            foreach ($newTariffData['prices'] as $newPriceBlock) {
                                if (!isset($newPriceBlock['from_date'], $newPriceBlock['to_date'], $newPriceBlock['price_arr'])) {
                                    continue;
                                }

                                $newFrom = DateTime::createFromFormat('Y-m-d', $newPriceBlock['from_date']);
                                $newTo = DateTime::createFromFormat('Y-m-d', $newPriceBlock['to_date']);

                                if (!$newFrom || !$newTo) {
                                    continue;
                                }

                                $newFromStr = $newFrom->format('d-m-Y');
                                $newToStr = $newTo->format('d-m-Y');
                                $newPriceArr = $newPriceBlock['price_arr'];
                                $updatedBlocks = [];

                                foreach ($existingPrices as $existingBlock) {
                                    if (!isset($existingBlock['from_date'], $existingBlock['to_date'], $existingBlock['price_arr'])) {
                                        continue;
                                    }

                                    $existFrom = DateTime::createFromFormat('d-m-Y', $existingBlock['from_date']);
                                    $existTo = DateTime::createFromFormat('d-m-Y', $existingBlock['to_date']);

                                    if (!$existFrom || !$existTo) {
                                        continue;
                                    }

                                    // No overlap
                                    if ($existTo < $newFrom || $existFrom > $newTo) {
                                        $updatedBlocks[] = $existingBlock;
                                        continue;
                                    }

                                    // Part before the overlap
                                    if ($existFrom < $newFrom) {
                                        $updatedBlocks[] = [
                                            'from_date' => $existFrom->format('d-m-Y'),
                                            'to_date' => (clone $newFrom)->modify('-1 day')->format('d-m-Y'),
                                            'price_arr' => $existingBlock['price_arr']
                                        ];
                                    }

                                    // Part after the overlap
                                    if ($existTo > $newTo) {
                                        $updatedBlocks[] = [
                                            'from_date' => (clone $newTo)->modify('+1 day')->format('d-m-Y'),
                                            'to_date' => $existTo->format('d-m-Y'),
                                            'price_arr' => $existingBlock['price_arr']
                                        ];
                                    }

                                    // Overlapping portion is skipped — replaced below
                                }

                                // Add the new price block
                                $updatedBlocks[] = [
                                    'from_date' => $newFromStr,
                                    'to_date' => $newToStr,
                                    'price_arr' => $newPriceArr
                                ];

                                // Sort final result by from_date
                                usort($updatedBlocks, function ($a, $b) {
                                    return strtotime(str_replace('-', '/', $a['from_date'])) - strtotime(str_replace('-', '/', $b['from_date']));
                                });

                                $existingPrices = $updatedBlocks;
                            }
                        }

                        $existingTariff['prices'] = $existingPrices;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    if (isset($newTariffData['id']) && isset($newTariffData['prices']) && is_array($newTariffData['prices'])) {
                        $existingTariffs[] = $newTariffData;
                    }
                }
            }

            $object['rooms'][$roomIndex]['tariff'] = array_values($existingTariffs);
            break;
        }

        $client->index('object')->updateDocuments([$object]);

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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'error' => 'Invalid request type'];
        }

        $tariffId = (int) Yii::$app->request->post('tariff_id');
        $checked = Yii::$app->request->post('checked') === 'true';
        $room_id = (int) Yii::$app->request->post('room_id');
        $object_id = (int) Yii::$app->request->post('object_id');

        if (!$tariffId || !$room_id || !$object_id) {
            return ['success' => false, 'error' => 'Missing required parameters'];
        }

        $tariff = Tariff::findOne($tariffId);
        if (!$tariff) {
            return ['success' => false, 'error' => 'Tariff not found'];
        }

        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);
        $rooms = $object['rooms'] ?? [];

        // Locate room index
        $roomIndex = null;
        foreach ($rooms as $index => $roomData) {
            if ((int) $roomData['id'] === $room_id) {
                $roomIndex = $index;
                break;
            }
        }

        if ($roomIndex === null) {
            return ['success' => false, 'error' => 'Room not found in Meilisearch'];
        }

        if ($checked) {
            // Add tariff
            $newTariff = [
                "id" => $tariffId,
                "title" => [$tariff->title, $tariff->title_en, $tariff->title_ky],
                "payment_on_book" => $tariff->payment_on_book ? 1 : 0,
                "payment_on_reception" => $tariff->payment_on_reception ? 1 : 0,
                "cancellation" => (int) $tariff->cancellation,
                "meal_type" => [
                    'id' => (int) $tariff->meal_type,
                    'name' => Objects::mealTypeFull($tariff->meal_type)
                ],
                "object_id" => $object_id,
                "prices" => []
            ];

            // Ensure tariffs array exists
            if (!isset($rooms[$roomIndex]['tariff']) || !is_array($rooms[$roomIndex]['tariff'])) {
                $rooms[$roomIndex]['tariff'] = [];
            }

            // Prevent duplicates
            $exists = array_filter($rooms[$roomIndex]['tariff'], function ($item) use ($tariffId) {
                return (int) $item['id'] === $tariffId;
            });

            if (empty($exists)) {
                $rooms[$roomIndex]['tariff'][] = $newTariff;
            }
        } else {
            // Remove tariff
            if (isset($rooms[$roomIndex]['tariff']) && is_array($rooms[$roomIndex]['tariff'])) {
                $rooms[$roomIndex]['tariff'] = array_values(array_filter(
                    $rooms[$roomIndex]['tariff'],
                    fn($item) => (int) $item['id'] !== $tariffId
                ));

                // If empty, remove the key
                if (empty($rooms[$roomIndex]['tariff'])) {
                    unset($rooms[$roomIndex]['tariff']);
                }
            }
        }

        foreach ($rooms as &$room) {
            if (isset($room['tariff']) && empty($room['tariff'])) {
                unset($room['tariff']);
            }
        }
        $object['rooms'] = $rooms;


        try {
            $response = $client->index('object')->updateDocuments([$object]);
            if (isset($response['taskUid'])) {
                return ['success' => true, 'task' => $response['taskUid']];
            } else {
                return ['success' => false, 'error' => 'Update sent but no task returned'];
            }
        } catch (\Throwable $e) {
            Yii::error("Meilisearch update failed: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }



    public function actionBindRoom()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'error' => 'Invalid request type'];
        }

        $tariffId = (int) Yii::$app->request->post('tariff_id');
        $checked = Yii::$app->request->post('checked') === 'true';
        $room_id = (int) Yii::$app->request->post('room_id');
        $object_id = (int) Yii::$app->request->post('object_id');

        if (!$tariffId || !$room_id || !$object_id) {
            return ['success' => false, 'error' => 'Missing required parameters'];
        }

        $tariff = Tariff::findOne($tariffId);
        if (!$tariff) {
            return ['success' => false, 'error' => 'Tariff not found'];
        }

        $client = Yii::$app->meili->connect();
        $object = $client->index('object')->getDocument($object_id);
        $rooms = $object['rooms'] ?? [];

        // Locate room index
        $roomIndex = null;
        foreach ($rooms as $index => $roomData) {
            if ((int) $roomData['id'] === $room_id) {
                $roomIndex = $index;
                break;
            }
        }

        if ($roomIndex === null) {
            return ['success' => false, 'error' => 'Room not found in Meilisearch'];
        }

        if ($checked) {
            // Add tariff
            $newTariff = [
                "id" => $tariffId,
                "title" => [$tariff->title, $tariff->title_en, $tariff->title_ky],
                "payment_on_book" => $tariff->payment_on_book ? 1 : 0,
                "payment_on_reception" => $tariff->payment_on_reception ? 1 : 0,
                "cancellation" => (int) $tariff->cancellation,
                "meal_type" => [
                    'id' => (int) $tariff->meal_type,
                    'name' => Objects::mealTypeFull($tariff->meal_type)
                ],
                "object_id" => $object_id,
                "prices" => []
            ];

            // Ensure tariffs array exists
            if (!isset($rooms[$roomIndex]['tariff']) || !is_array($rooms[$roomIndex]['tariff'])) {
                $rooms[$roomIndex]['tariff'] = [];
            }

            // Prevent duplicates
            $exists = array_filter($rooms[$roomIndex]['tariff'], function ($item) use ($tariffId) {
                return (int) $item['id'] === $tariffId;
            });

            if (empty($exists)) {
                $rooms[$roomIndex]['tariff'][] = $newTariff;
            }
        } else {
            // Remove tariff
            if (isset($rooms[$roomIndex]['tariff']) && is_array($rooms[$roomIndex]['tariff'])) {
                $rooms[$roomIndex]['tariff'] = array_values(array_filter(
                    $rooms[$roomIndex]['tariff'],
                    fn($item) => (int) $item['id'] !== $tariffId
                ));

                // If empty, remove the key
                if (empty($rooms[$roomIndex]['tariff'])) {
                    unset($rooms[$roomIndex]['tariff']);
                }
            }
        }

        foreach ($rooms as &$room) {
            if (isset($room['tariff']) && empty($room['tariff'])) {
                unset($room['tariff']);
            }
        }
        $object['rooms'] = $rooms;


        try {
            $response = $client->index('object')->updateDocuments([$object]);
            if (isset($response['taskUid'])) {
                return ['success' => true, 'task' => $response['taskUid']];
            } else {
                return ['success' => false, 'error' => 'Update sent but no task returned'];
            }
        } catch (\Throwable $e) {
            Yii::error("Meilisearch update failed: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
        }
    }


}

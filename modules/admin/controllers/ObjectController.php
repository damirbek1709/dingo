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
                    'actions' => ['index', 'view', 'update', 'create', 'bind-tariff', 'bind-room', 'send-to-moderation'],
                    'roles' => ['admin'],
                ],
            ]
        ];

        return $behaviors;
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

                $status = Objects::currentStatus($object_id, $model->status ? $model->status : Objects::STATUS_NOT_PUBLISHED);

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
                    'email' => $model->email,
                    'features' => $model->features ?? [],
                    'images' => $model->getPictures(),
                    'general_room_count' => $model->general_room_count,
                    'status' => $status
                ];

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
            'bind_model' => $bind_model
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

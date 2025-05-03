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
class ObjectController extends Controller{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'update', 'create', 'bind-tariff','bind-room','send-to-moderation'],
                    'roles' => ['admin'],
                ],
            ]
        ];

        return $behaviors;
    }


    public function actionIndex()
    {
        $filter_string = "";
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

    public function actionSendToModeration(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('object_id');
        $status = Yii::$app->request->post('status');
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);
       
        if($index->updateDocuments([
            'id' => $id,
            'status' => $status
        ])){
            return Objects::statusData($status);
        }
        return false;
    }
}

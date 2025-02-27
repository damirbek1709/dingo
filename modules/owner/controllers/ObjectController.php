<?php

namespace app\modules\owner\controllers;

use Yii;
use app\models\Objects;
use app\models\Comfort;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use yii\web\UploadedFile;
use app\models\PaymentType;
/**
 * EventController implements the CRUD actions for Event model.
 */
class ObjectController extends Controller
{
    public $layout;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Event models.
     * @return mixed
     */
    public function actionIndex()
    {
        $filter_string = '';
        $key_word = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'keyword') ? ArrayHelper::getValue(Yii::$app->request->bodyParams, 'keyword') : '';
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->search($key_word, [
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

    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $this->layout = 'main';
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
        $model = new Event();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();
        if (empty($searchResult)) {
            throw new \yii\web\NotFoundHttpException('Record not found.');
        }

        $model = new Objects($searchResult[0]);

        if (isset($comfort_list)) {
            //echo "<pre>";print_r($comfort_list);echo "</pre>";die();
            $comfort_models = Comfort::find()->where(['id' => $comfort_list])->all();
            $comfortArr = [];
            foreach ($comfort_models as $item) {
                $comfortArr[$item->category_id][] = [$item->id => $item->title];
            }

            $meilisearchData = [
                'id' => $id,
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
            $model->children = Yii::$app->request->post('children', []);
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
                    'meal_purchaise'=>(bool) $model->meal_purchaise,
                    'children'=>array_values($model->children),
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

    public function actionAddRoom($id){
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

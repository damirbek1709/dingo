<?php

namespace app\controllers;

use Yii;
use app\models\Place;
use app\models\PlaceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use dektrium\user\filters\AccessRule;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;

/**
 * PageController implements the CRUD actions for Page model.
 */
class PlaceController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'roleAccess' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view','index'],
                        'roles' => ['?','@']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create','update','admin','delete'],
                        'roles' => ['admin']
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionAdmin()
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
     * Creates a new Page model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Place();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['admin']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

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
        $model = new Place($searchResult[0]);

        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Normally, update would be in a DB, but for Meilisearch, we update the index
            $model->images = UploadedFile::getInstances($model, 'images');
            $model->primaryKey = ['id'];
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }
            $index->updateDocuments([$model->attributes]);

            if ($model->img) {
                $image_id = $model->img;
                foreach ($this->getImages() as $image) {
                    if ($image->id == $image_id) {
                        $this->setMainImage($image);
                    }
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $client = Yii::$app->meili->connect();
        $index = $client->index('object'); // Replace with your actual Meilisearch index

        // Fetch record from Meilisearch
        $searchResult = $index->search('', ['filter' => "id = $id"])->getHits();

        if (empty($searchResult)) {
            throw new NotFoundHttpException('Record not found.');
        }

        // Convert the result into a Yii2 model
        $model = new Place();

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    
    /**
     * Deletes an existing Page model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['admin']);
    }

    /**
     * Finds the Page model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Place the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Place::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

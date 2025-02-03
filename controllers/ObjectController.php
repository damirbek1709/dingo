<?php

namespace app\controllers;
use app\models\Objects;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;


class ObjectController extends \yii\web\Controller
{
    public function actionDelete()
    {
        return $this->render('delete');
    }

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

        //echo "<pre>";print_r($res->getHits());echo "</pre>";die();

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
            // Normally, update would be in a DB, but for Meilisearch, we update the index
            $index->updateDocuments([$model->attributes]);
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
        $model = new Objects($searchResult[0]);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

}

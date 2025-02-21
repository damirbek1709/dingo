<?php

namespace app\modules\admin\controllers;

use Yii;
use Throwable;
use app\models\Directory;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\DirectorySearch;
use app\models\DirectoryOption;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use app\models\DirectoryOptionSearch;
use himiklab\sortablegrid\SortableGridAction;

/**
 * DirectoryController implements the CRUD actions for Directory model.
 */
class DirectoryController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'sort' => [
                'class' => SortableGridAction::className(),
                'modelName' => DirectoryOption::className(),
            ],
        ];
    }

    /**
     * Lists all Directory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DirectorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Directory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $directoryOptionSearchModel = new DirectoryOptionSearch();
        $directoryOptionSearchModel->directory_id = $model->id;
        $directoryOptionDataProvider = $directoryOptionSearchModel->search(Yii::$app->request->queryParams);
        $directoryOptionDataProvider->sort->defaultOrder = ['position' => SORT_ASC];
        $directoryOptionDataProvider->pagination = false;

        return $this->render('view', [
            'model' => $model,
            'directoryOptionSearchModel' => $directoryOptionSearchModel,
            'directoryOptionDataProvider' => $directoryOptionDataProvider,
        ]);
    }

    /**
     * Creates a new Directory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Directory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Directory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Directory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Directory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Directory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Directory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
    }
}

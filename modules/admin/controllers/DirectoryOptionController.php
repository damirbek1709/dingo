<?php

namespace app\modules\admin\controllers;

use Yii;
use Throwable;
use app\models\Directory;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\DirectoryOption;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use app\models\DirectoryOptionSearch;

/**
 * DirectoryOptionController implements the CRUD actions for DirectoryOption model.
 */
class DirectoryOptionController extends BaseController
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
     * Lists all DirectoryOption models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DirectoryOptionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DirectoryOption model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DirectoryOption model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $directory_id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCreate($directory_id)
    {
        $directory = $this->findDirectory($directory_id);
        $model = new DirectoryOption();
        $model->directory_id = $directory->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing DirectoryOption model.
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
     * Deletes an existing DirectoryOption model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $directory = $model->directory;
        $model->delete();

        return $this->redirect(['directory/view', 'id' => $directory->id]);
    }

    /**
     * Finds the Directory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Directory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findDirectory($id)
    {
        if (($model = Directory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
    }

    /**
     * Finds the DirectoryOption model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DirectoryOption the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DirectoryOption::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
    }
}

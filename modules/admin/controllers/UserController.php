<?php

namespace app\modules\admin\controllers;


use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Booking;
use app\models\user\User;
use app\models\user\UserSearch;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public $layout;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                    //'delete'     => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index','update'],
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }
    /**
     * Shows user's profile.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */

    public function actionUpdate($id)
    {
        $this->layout = "/general";
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)){
                return $this->redirect(['index']);
            }
        }
        return $this->render('update', [
            'model'=>$model
        ]);
        
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не найдена.');
    }



    public function actionIndex($category = null)
    {
        $this->layout = "/general";
        $searchModel = Yii::createObject(UserSearch::className());
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        $client = Yii::$app->meili->connect();
        $documents = $client->index('object')->search('', [
            'limit' => 1
        ]);
        $userIds = [];
        foreach ($documents as $doc) {
            if (isset($doc['user_id'])) {
                $userIds[] = $doc['user_id'];
            }
        }

        // Optionally, get unique user_ids
        $uniqueUserIds = array_values(array_unique($userIds));
        $dataProvider->query->where(['id' => $uniqueUserIds]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

}

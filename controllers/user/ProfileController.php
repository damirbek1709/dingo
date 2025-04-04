<?php

namespace app\controllers\user;

use app\models\user\User;
use dektrium\user\controllers\ProfileController as BaseProfileController;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\Post;
use app\models\PostSearch;

class ProfileController extends BaseProfileController
{
    public $layout;
    /**
     * Shows user's profile.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['index','delete-profile'], 'roles' => ['@']],
                    ['allow' => true, 'actions' => ['show', 'view'], 'roles' => ['?', '@']],
                ],
            ],
        ];
    }

    public function actionShow($status = Post::STATUS_ACTIVATED)
    {
        $this->layout = "//index";
        //$id = Yii::$app->user->identity->profile->id;
        $profile = $this->finder->findProfileById(Yii::$app->user->id);

        $searchModel = new PostSearch();
        $searchModel->user_id = Yii::$app->user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true, null);
        $dataProvider->query->andFilterWhere(['status' => $status]);

        if ($profile === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('show', [
            'profile' => $profile,
            'dataProvider' => $dataProvider,
            'status' => $status
        ]);
    }

    public function actionView($status = Post::STATUS_ACTIVATED)
    {
        $this->layout = "//profile";
        $id = Yii::$app->user->id;
        $profile = $this->finder->findProfileById($id);

        if ($profile === null) {
            throw new NotFoundHttpException();
        }

        $searchModel = new PostSearch();
        $searchModel->user_id = Yii::$app->user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true, null);
        $dataProvider->query->andFilterWhere(['status' => $status]);

        return $this->render('profile', [
            'profile' => $profile,
            'user_id' => $id,
            'dataProvider' => $dataProvider,
            'status' => $status
        ]);
    }

    public function actionDeteleProfile(){
        $user = User::findOne(Yii::$app->user->id);
        $user->username = preg_replace('/^996/', rand(100, 999), $user->username);
        $user->full_name = "Deleted User";
        $user->confirmed_at = null;

        $time = time();
        $length = strlen($user->auth_key) - strlen($time);

        $user->auth_key = substr($user->auth_key, 0, $length) . $time;

        $user->flags = User::FLAG_USER_DELETED;
        $dao = Yii::$app->db;

        if ($user->save()) {
            $dao->createCommand()->delete('fcm_token', ['user_id' => $user->id, 'app_id' => 1])->execute();
            return $this->redirect(['/site/index']);
        }
    }
}

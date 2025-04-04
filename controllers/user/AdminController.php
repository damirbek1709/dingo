<?php

namespace app\controllers\user;

use app\models\BusinessAccountBridge;
use app\models\BusinessAccountBridgeSearch;
use app\models\PaidService;
use Yii;
use app\models\user\User;
use app\models\user\UserSearch;
use dektrium\user\controllers\AdminController as BaseAdminController;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

class AdminController extends BaseAdminController
{
    public $layout = '@app/modules/moderator/views/layouts/main';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->fillBreadcrumbs();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Fill common breadcrumbs
     */
    protected function fillBreadcrumbs()
    {
        $breadcrumbs = [];

        $module = Yii::$app->getModule('moderator');
        $label = $module->name;
        $breadcrumbs[] = $this->route == 'admin/default/index' ? $label : [
            'label' => $label,
            'url' => ['/admin'],
        ];

        $this->mergeBreadCrumbs($breadcrumbs);
    }

    /**
     * Prepend common breadcrumbs to existing ones
     * @param array $breadcrumbs
     */
    protected function mergeBreadcrumbs($breadcrumbs)
    {
        if (Yii::$app->controller->action->id !== 'error') {
            $existingBreadcrumbs = ArrayHelper::getValue($this->view->params, 'breadcrumbs', []);
            $this->view->params['breadcrumbs'] = array_merge($breadcrumbs, $existingBreadcrumbs);
        }
    }

    public function actionVerify($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            if($model->verified_status == User::VERIFIED_STATUS_SUCCEED){
                $this->sendVerifyPush($id,);
            }
            
            return $this->redirect(['verify-list', 'id' => $model->id]);
        }

        return $this->render('verify', [
            'model' => $model,
        ]);
    }

    protected function sendVerifyPush($user_id)
    {   
        $data = [
            'user_id' => $user_id,
            'user_verified_status' => Yii::t('app','Ваш аккаунт верифицирован'),
        ];
        $params = [
            'notif' => ['title' => 'Внимание', 'body' => Yii::t('app','Ваш аккаунт верифицирован'), 'user_id' => $user_id],
            'data' => $data,
        ];

        $token_rows = Yii::$app->db->createCommand("SELECT * FROM fcm_token WHERE user_id='{$user_id}'")->queryAll();
        foreach ($token_rows as $tokenRow) {
            User::pushNotification($tokenRow['token'], $params);
        }
    }

    public function actionVerifyList()
    {
        $query = User::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andFilterWhere([
            'verified_status' => [User::VERIFIED_STATUS_PENDING, User::VERIFIED_STATUS_SUCCEED],
        ]);

        return $this->render('verify_index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionEditBusinessAccount($id)
    {
        $this->layout = '@app/modules/admin/views/layouts/main';
        $model = User::findOne($id);
        $business_account = BusinessAccountBridge::find()->where(['user_id' => $model->id])->one();        
        if ($business_account && $business_account->load(Yii::$app->request->post()) && $business_account->save(false)) {
            return $this->redirect(['business-account-list']);
        }

        return $this->render('business_account_edit', [
            'model' => $model,
            'b_account' => $business_account
        ]);
    }

    public function actionCreateBusinessAccount()
    {
        $this->layout = '@app/modules/admin/views/layouts/main';
        $business_account = new BusinessAccountBridge();       
        if ($business_account->load(Yii::$app->request->post()) && $business_account->save()) {
            return $this->redirect(['business-account-list']);
        }

        return $this->render('business_account_create', [
            'b_account' => $business_account
        ]);
    }

    public function actionBusinessAccountListOld()
    {
        $this->layout = '@app/modules/admin/views/layouts/main';
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $params = Yii::$app->request->queryParams;
        if ($params && isset($params["UserSearch"])) {
            $username = $params["UserSearch"]["user_username"];
            $id = $params["UserSearch"]["id"];
            $b_account = $params["UserSearch"]["business_account"];

            
            if ($username) {
                $dataProvider->query->andFilterWhere(['user.username' => $username]);
            }
            
            if ($b_account) {
                $dataProvider->query->andFilterWhere(['business_account' => $b_account]);
            }
        }
        return $this->render('business_account_index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    public function actionBusinessAccountList()
    {
        $this->layout = '@app/modules/admin/views/layouts/main';
        $searchModel = new BusinessAccountBridgeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        

        $params = Yii::$app->request->queryParams;
        if ($params && isset($params["UserSearch"])) {
            $full_name = $params["UserSearch"]["full_name"];
            $username = $params["UserSearch"]["username"];
            $id = $params["UserSearch"]["id"];
            $b_account = $params["UserSearch"]["business_account_id"];
            
            $dataProvider->query->where(['>=','active_until', date('Y-m-d H:i:s')]);

            if ($full_name) {
                $dataProvider->query->andFilterWhere(['like', 'user.full_name', $full_name]);
            }
            if ($username) {
                $dataProvider->query->andFilterWhere(['user.username' => $username]);
            }
            if ($id) {
                $dataProvider->query->andFilterWhere(['user.id' => $id]);
            }
            if ($b_account) {
                $dataProvider->query->andFilterWhere(['business_account_id' => $b_account]);
            }
        }
        return $this->render('business_account_index_demo', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
}

<?php

namespace app\modules\api\controllers;

use app\models\Booking;
use app\models\user\UserStatus;
use Yii;
use app\models\user\SignupForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\user\User;
use yii\helpers\ArrayHelper;
use app\models\user\Token;
use app\models\user\RegistrationForm;
use yii\web\NotFoundHttpException;
use app\models\Favorite;
use app\models\Vocabulary;

class UserController extends BaseController
{
    public $modelClass = 'app\models\UserModel';
    public function actionCustomAction()
    {
        return $this->render('custom-view');
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        // Load data into the model
        $data = Yii::$app->request->post();
        $model->load($data, '');

        if ($model->validate() && $model->signup()) {
            return [
                'success' => true,
                'message' => '',
            ];
        }

        return [
            'success' => false,
            'errors' => $model->getErrors(),
        ];
    }

    public function actionRegister()
    {
        $response["success"] = false;

        $model = Yii::createObject(RegistrationForm::className());
        $email = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $user = User::find()->where(['email' => $email])->one();

        if (!$user) {
            $user = new User();
            $user->username = $email;
            $user->email = $email;

            if ($user->register()) {

                $auth = Yii::$app->authManager;
                $role = $auth->getPermission('owner'); // Make sure "owner" role exists in RBAC
                if ($role) {
                    $auth->assign($role, $user->id);
                }

                $response["success"] = true;
                $response["message"] = "Пользователь создан";
                if (in_array($email, ['damirbek@gmail.com', 'adiletprosoft@gmail.com'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                    $response['code'] = $token->code;
                    Yii::$app->mailer->compose()
                        ->setFrom('send@dingo.kg')
                        ->setTo($email)
                        ->setSubject("Ваш код авторизации: " . $token->code)
                        ->setHtmlBody("<h1>{$token->code}</h1>")
                        ->setTextBody('Hello from Resend! This is a test email.')
                        ->send();

                }
            }
        } else {
            $sendSMS = true;
            $response["success"] = true;
            $response["message"] = "Пользователь найден";
            if (in_array($email, ['damirbek@gmail.com', 'adiletprosoft@gmail.com'])) {
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                $sendSMS = false;
            } else {
                $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $user);
                //$response['code'] = $token->code;
            }
            if ($sendSMS) {
                Yii::$app->mailer->compose()
                    ->setFrom('send@dingo.kg')
                    ->setTo($email)
                    ->setSubject("Ваш код авторизации: " . $token->code)
                    ->setHtmlBody("<h1>{$token->code}</h1>")
                    ->setTextBody('Hello from Resend! This is a test email.')
                    ->send();
            }
        }

        return $response;
    }



    public function actionCheckConfirmationCode()
    {
        $module = Yii::$app->getModule('user');

        if (!$module->enableConfirmation) {
            throw new NotFoundHttpException();
        }

        $response["success"] = false;

        $code = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'code');
        $email = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'email');
        $user = User::find()->where(['email' => $email])->one();

        $token = Token::find()->where(['code' => $code, 'user_id' => $user->id, 'type' => Token::TYPE_CONFIRMATION])->one();

        if ($token === null || $token->isExpired || $token->user === null) {
            $response["success"] = false;
            $response["errors"]["code"] = 'Проверочный код не найден или устарел';
        } else {
            $user = $token->user;
            $user->confirmed_at = time();
            $user->save();
            $token->delete();

            $response["success"] = true;
            $response["message"] = 'Номер телефона подтверждён';
            $response["user"] = $user;

            //$this->saveFcm($user->id, Yii::$app->request->post());
        }

        return $response;
    }

    public function actionAddToFavorites($id)
    {
        $response["success"] = false;
        $favorite = new Favorite();
        $favorite->object_id = $id;
        $favorite->user_id = Yii::$app->user->id;
        if ($favorite->save()) {

            $response["success"] = true;
            $response["message"] = Yii::t('app', 'Объект добавлен в избранные');
        }
        return $response;
    }

    public function actionFavorites()
    {
        $fav_arr = ArrayHelper::map(
            Favorite::find()->where(['user_id' => Yii::$app->user->id])->all(),
            'id',
            'object_id'
        );

        $ids = array_values($fav_arr);
        $totalCount = count($ids);
        $pageSize = 10;
        $page = max(1, (int) Yii::$app->request->get('page', 1));
        $offset = ($page - 1) * $pageSize;

        if (empty($ids) || $offset >= $totalCount) {
            return [
                'pageSize' => $pageSize,
                'totalCount' => $totalCount,
                'page' => $page,
                'data' => [],
            ];
        }

        $paginatedIds = array_slice($ids, $offset, $pageSize);
        $idFilter = 'id IN [' . implode(', ', $paginatedIds) . ']';

        $client = Yii::$app->meili->connect();
        $searchResults = $client->index('object')->search('', [
            'filter' => [$idFilter],
            'limit' => $pageSize,  // not necessary but safe
            'offset' => 0,         // since you're paginating IDs manually
        ]);

        $hits = $searchResults->getHits();

        // Preload Vocabulary data to avoid N+1 queries
        $typeIds = array_unique(array_column($hits, 'type'));
        $types = Vocabulary::find()
            ->select(['id', 'title', 'title_en', 'title_ky'])
            ->where(['id' => $typeIds])
            ->indexBy('id')
            ->asArray()
            ->all();

        foreach ($hits as &$hit) {
            $type_id = $hit['type'] ?? null;
            if ($type_id && isset($types[$type_id])) {
                $type = $types[$type_id];
                $hit['type_string'] = [$type['title'], $type['title_en'], $type['title_ky']];
            } else {
                $hit['type_string'] = null;
            }
        }
        unset($hit); // break reference

        return [
            'pageSize' => $pageSize,
            'totalCount' => $totalCount, // ✅ keep this from PHP, not Meilisearch
            'page' => $page,
            'data' => $hits,
        ];
    }





    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'signup',
            'register',
            'check-confirmation-code'
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['signup', 'register', 'check-confirmation-code'],
                    'roles' => ['?'],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete-account', 'edit-account', 'add-to-favorites', 'favorites'],
                    'roles' => ['@'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'signup' => ['POST'],
                'register' => ['POST'],
                'check-confirmation-code' => ['POST'],
                'delete-account' => ['POST'],
                'edit-account' => ['POST'],
                'favorites' => ['GET'],
                'add-to-favorites' => ['GET'],
            ],
        ];


        return $behaviors;
    }

    // public function getActiveBookings(){
    //     $bookings = Booking::find()->all();
    // }

    public function actionDeleteAccount()
    {
        $response["success"] = false;
        $user = User::findOne(Yii::$app->user->id);
        $user->confirmed_at = null;
        $rand = $user->id . rand(1000, 9999);
        $user->username = "deleted_user_" . $rand;
        $user->email = "deleted_user_" . $rand;
        $user->name = "Deleted User";

        $user->flags = User::FLAG_DELETED;
        $booking = Booking::find()->where(['user_id' => Yii::$app->user->id, 'status' => Booking::PAID_STATUS_PAID])->andWhere(['>', 'date_from', date('Y-m-d')])->one();
        if ($user->save(false)) {
            $response["message"] = Yii::t("app", "Удаление учётной записи прошло успешно");
            if ($booking) {
                $response["message"] = Yii::t("app", "Удаление учётной записи прошло успешно, но на вашем объекте действующая бронь, ");
            }
            $response["success"] = true;
        }
        return $response;
    }

    public function actionEditAccount()
    {
        $response["success"] = false;
        $user = User::findOne(Yii::$app->user->id);
        $name = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'name') ?? "";
        $user->name = $name;
        if ($user->save(false)) {
            $response["message"] = Yii::t("app", "Изменения сохранены");
            $response["success"] = true;
            $response["data"] = Yii::$app->user->identity;
        }
        return $response;
    }
}

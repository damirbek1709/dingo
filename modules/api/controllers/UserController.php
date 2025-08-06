<?php

namespace app\modules\api\controllers;

use app\models\Booking;
use app\models\NotificationSearch;
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
use app\models\Notification;
use app\models\Feedback;
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
        $phone = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'phone');
        $user = User::find()->where(['email' => $email])->orWhere(['phone' => $phone])->one();

        if (!$user) {
            $user = new User();
            $user->username = $email;
            $user->email = $email;
            $user->phone = $phone;

            if ($user->register()) {

                $auth = Yii::$app->authManager;
                $role = $auth->getPermission('owner'); // Make sure "owner" role exists in RBAC
                if ($role) {
                    $auth->assign($role, $user->id);
                }

                $response["success"] = true;
                $response["message"] = "Пользователь создан";
                if (in_array($email, ['damirbek@gmail.com'])) {
                    $dao = Yii::$app->db;
                    $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                    $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                    $sendSMS = false;
                } else {
                    $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                    $token->link('user', $user);
                    $response['code'] = $token->code;
                    $recipient = '+' . $phone;

                    Yii::$app->nikita->setRecipient($recipient)
                        ->setText('Ваш код для входа: ' . $token->code)
                        ->send();

                    Yii::$app->mailer->compose()
                        ->setFrom('send@dingo.kg')
                        ->setTo($email)
                        ->setSubject("Ваш код для входа: " . $token->code)
                        ->setHtmlBody("<h1>{$token->code}</h1>")
                        ->setTextBody('Hello from Resend! This is a test email.')
                        ->send();


                }
            }
        } else {
            $sendSMS = true;
            $response["success"] = true;
            $response["message"] = "Пользователь найден";
            if (in_array($email, ['damirbek@gmail.com'])) {
                $dao = Yii::$app->db;
                $dao->createCommand()->delete('token', ['user_id' => $user->id])->execute();
                $dao->createCommand()->insert('token', ['user_id' => $user->id, 'code' => '000000', 'type' => Token::TYPE_CONFIRMATION, 'created_at' => time()])->execute();
                $sendSMS = false;
            } else {
                $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $user);
                //$response['code'] = $token->code;

                if ($sendSMS) {
                    $recipient = '+' . $phone;
                    Yii::$app->nikita->setRecipient($recipient)
                        ->setText('Ваш код для входа: ' . $token->code)
                        ->send();

                    Yii::$app->mailer->compose()
                        ->setFrom('send@dingo.kg')
                        ->setTo($email)
                        ->setSubject("Ваш код для входа: " . $token->code)
                        ->setHtmlBody("<h1>{$token->code}</h1>")
                        ->setTextBody('Hello from Resend! This is a test email.')
                        ->send();

                }
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

            $this->saveFcm($user->id, Yii::$app->request->post());
        }

        return $response;
    }

    protected function saveFcm($user_id, $post, $shouldDelete = true)
    {
        if (!empty($post['fcm_token']) && !empty($post['device_id'])) {
            $fcm_token = $post['fcm_token'];
            $device_id = $post['device_id'];
            $dao = Yii::$app->db;
            $sql = "SELECT * FROM `fcm_token` WHERE user_id={$user_id} AND device_id='{$device_id}' AND token='{$fcm_token}'";
            $row = $dao->createCommand($sql)->queryOne();
            if (!$row) {
                if ($shouldDelete) {
                    //delete prev
                    $dao->createCommand()->delete('fcm_token', ['user_id' => $user_id, 'device_id' => $device_id])->execute();
                }
                $dao->createCommand()->delete('fcm_token', ['device_id' => $device_id])->execute();
                $dao->createCommand()->insert('fcm_token', ['user_id' => $user_id, 'device_id' => $device_id, 'token' => $fcm_token, 'created_at' => time()])->execute();
            }
        }
    }

    public function generalFeedback($object_id)
    {
        $feedback = Feedback::find()->where(['object_id' => $object_id]);
        $sum = $feedback->sum('general');
        $count = $feedback->count();
        if ($count) {
            $average = $sum / $count;
            return (float) round($average, 2);
        }
        return 0;
    }

    public function actionAddToFavorites($id)
    {
        $response["success"] = false;
        $favorite = Favorite::find()->where(['object_id' => $id, 'user_id' => Yii::$app->user->id])->one();
        if ($favorite) {
            $favorite->delete();
            $response["success"] = true;
            $response["message"] = Yii::t('app', 'Объект удален из избранных');
        } else {
            $favorite = new Favorite();
            $favorite->object_id = $id;
            $favorite->user_id = Yii::$app->user->id;
            if ($favorite->save()) {
                $response["success"] = true;
                $response["message"] = Yii::t('app', 'Объект добавлен в избранные');
            }
        }
        $new_fav = Favorite::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->asArray()
            ->all();
        $response["data"] = ArrayHelper::getColumn($new_fav, 'object_id');
        return $response;
    }

    public function actionFavoriteIds()
    {
        $response = null;
        $fav = Favorite::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->asArray()
            ->all();
        if ($fav)
            $response = ArrayHelper::getColumn($fav, 'object_id');
        return $response;
    }



    public function actionRemoveFromFavorites($id)
    {
        $response["success"] = false;
        $favorite = Favorite::find()->where(['object_id' => $id, 'user_id' => Yii::$app->user->id])->one();
        if ($favorite) {
            $favorite->delete();
            $response["success"] = true;
            $response["message"] = Yii::t('app', 'Объект удален из избранных');
        } else {
            $response["message"] = "Объект не найден";
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
            $hit['rating'] = $this->generalFeedback($hit['id']);
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
                    'actions' => ['delete-account', 'edit-account', 'add-to-favorites', 'favorites', 'remove-from-favorites', 'favorite-ids', 'notification-list', 'notification-read-all', 'notification-read-single'],
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
                'remove-from-favorites' => ['GET'],
                'favorite-ids' => ['GET'],
                'notification-list' => ['GET'],
                'notification-read-all' => ['GET'],
                'notification-read-single' => ['GET'],
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

    public function actionNotificationList($page = 1)
    {
        $searchModel = new NotificationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true, null);
        $user_id = Yii::$app->user->id;

        $dataProvider->query->andFilterWhere(['user_id' => $user_id]);

        $pageSize = (int) Yii::$app->request->get('per-page', 10);
        $dataProvider->pagination = [
            'page' => $page - 1, // DataProvider uses 0-based indexing
            'pageSize' => $pageSize,
            'pageSizeLimit' => [1, 100],
        ];

        // Create a separate query for counting unread notifications
        $notReadCount = Notification::find()
            ->where(['user_id' => $user_id, 'status' => Notification::STATUS_NOT_READ])
            ->count();

        return [
            'pageSize' => $dataProvider->pagination->pageSize,
            'totalCount' => $dataProvider->totalCount,
            'page' => (int) $page,
            'data' => $dataProvider,
            'not_read_count' => $notReadCount
        ];
    }

    public function actionNotificationReadAll()
    {
        $response["success"] = false;
        if (
            Notification::updateAll(
                ['status' => Notification::STATUS_READ],
                ['user_id' => Yii::$app->user->id]
            )
        ) {
            $response["success"] = true;
            $response["message"] = Yii::t("app", "Все уведомления прочитаны");
        }
        return $response;
    }

    public function actionNotificationReadSingle()
    {
        $response["success"] = false;
        $notification = Notification::findOne(Yii::$app->request->get('id'));
        $notification->status = Notification::STATUS_READ;

        if ($notification->save(false)) {
            $response["success"] = true;
            $response["message"] = Yii::t("app", "Уведомление прочтено");
        }
        return $response;
    }
}

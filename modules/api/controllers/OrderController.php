<?php

namespace app\modules\api\controllers;

use Yii;
use app\models\Order;
use app\models\OrderSearch;
use app\models\OrderItem;
use app\models\Post;
use app\models\user\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\models\Chat;
use app\models\Chatline;
use app\models\OrderItemSearch;
use app\models\CourierBridge;
use app\models\CourierInfo;
use Countable;
use yii\web\UploadedFile;
use app\models\Rating;
use app\models\CourierRating;

class OrderController extends BaseController
{

    public $modelClass = 'app\models\Order';

    public function actionChangeStatus()
    {
        $response["result"] = false;
        $response["message"] = "Возникла ошибка";
        $order_id = Yii::$app->request->post('order_id');
        $order_status = Yii::$app->request->post('order_status');


        $order = Order::findOne($order_id);
        if ($order) {
            $order->status = $order_status;
            if ($order->save()) {
                $response["result"] = true;
                $response["message"] = "Статус заказа был изменен";
            } else {
                $response["message"] = $order->errors;
            }
        } else {
            $response["message"] = "Заказ не найден в системе";
        }

        return $response;
    }

    public function actionAdd()
    {
        if (Yii::$app->request->post()) {
            return Yii::$app->request->post();
        }
        return "Oleeee yesss";
    }

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'add',
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['change-status', 'add'],
                    'roles' => ['admin', '@', '?'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'change-status' => ['POST'],
                'add-order' => ['POST'],
            ],
        ];

        return $behaviors;
    }
}

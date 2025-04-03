<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Freedom;
use yii\httpclient\Client;
use app\models\Product;
use app\components\ResendClient;
class SiteController extends Controller
{


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'image-upload' => [
                'class' => 'vova07\imperavi\actions\UploadFileAction',
                'url' => Yii::$app->urlManager->createUrl('uploads/images/site/redactor'),
                // Directory URL address, where files are stored.
                'path' => '@webroot/uploads/images/site/redactor',
                // Or absolute path to directory where files are stored.
            ],
            'file-upload' => [
                'class' => 'vova07\imperavi\actions\UploadFileAction',
                'url' => Yii::$app->urlManager->createUrl('uploads/images/site/redactor'),
                // Directory URL address, where files are stored.
                'path' => '@webroot/uploads/images/site/redactor',
                // Or absolute path to directory where files are stored.
                'uploadOnlyImage' => false,
                // For any kind of files uploading.
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionShipment()
    {
        return $this->render('page', ['id' => 1]);
    }

    public function actionAbout()
    {
        return $this->render('page', ['id' => 2]);
    }

    public function actionPayment()
    {
        return $this->render('page', ['id' => 3]);
    }

    public function actionSelfPickup()
    {
        return $this->render('page', ['id' => 4]);
    }

    public function actionReturn()
    {
        return $this->render('page', ['id' => 5]);
    }



    public function actionClients()
    {
        return $this->render('page', ['id' => 8]);
    }



    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }



    public function actionFreedomCheck()
    {
        $req = Yii::$app->request;
        $post = $req->post();
        $get = $req->get();
        $dao = Yii::$app->db;
        $date = date('Y-m-d H:i');
        if ($post) {
            $dao->createCommand()->insert('test', ['title' => 'freedom check post ' . $date, 'desc' => json_encode($post)])->execute();
        }
        if ($get) {
            $dao->createCommand()->insert('test', ['title' => 'freedom check get ' . $date, 'desc' => json_encode($get)])->execute();
        }
    }



    public function actionFreedomResult()
    {
        //Yii::$app->response->format = Response::FORMAT_XML;
        $req = Yii::$app->request;
        $post = $req->post();
        $dao = Yii::$app->db;
        $date = date('Y-m-d H:i');
        if ($post) {
            $dao->createCommand()->insert('test', ['title' => 'result post ' . $date, 'desc' => json_encode($post)])->execute();
            if (!isset($post['pg_payment_id'])) {

                $dao->createCommand()->insert('test', ['title' => 'pg_payment_id not set' . $date, 'desc' => json_encode($post)])->execute();
                return;
            }
            if ($model = Freedom::find()->where(['pg_payment_id' => $post['pg_payment_id']])->one()) {
                if (isset($post['pg_testing_mode'])) {
                    $model->pg_testing_mode = $post['pg_testing_mode'];
                }
                $model->pg_payment_method = $post['pg_payment_method'];
                $model->pg_transaction_status = $post['pg_result'];
                $model->pg_net_amount = $post['pg_net_amount'];
                if (isset($post['pg_user_phone'])) {
                    $model->pg_user_phone = $post['pg_user_phone'];
                }
                if (isset($post['pg_card_owner'])) {
                    $model->pg_card_owner = $post['pg_card_owner'];
                }
                if (isset($post['pg_captured'])) {
                    $model->pg_captured = $post['pg_captured'];
                } else {
                    $model->pg_captured = 0;
                }
                if (isset($post['pg_card_pan'])) {
                    $model->pg_card_pan = $post['pg_card_pan'];
                }
                if ($model->save()) {
                    return true;
                } else {
                    $dao->createCommand()->insert('test', ['title' => 'not saved' . $date, 'desc' => json_encode($model->getErrors())])->execute();
                }
            } else {

                $dao->createCommand()->insert('test', ['title' => 'payment not found' . $date, 'desc' => json_encode($post)])->execute();
            }
        }
    }

    public function actionSendEmail()
    {
        Yii::$app->mailer->compose()
            ->setFrom('info@green-alliance.kg')
            ->setTo('damirbek@gmail.com')
            ->setSubject('Test Email')
            ->setHtmlBody('<h1>Hello from Resend!</h1><p>This is a test email.</p>')
            ->setTextBody('Hello from Resend! This is a test email.')
            ->send();
    }

    public function actionThankyou()
    {
        // $request = Yii::$app->request->get();
        // //echo "<pre>";print_r($request);echo "</pre>";die();
        // if ($request) {
        //     $order_id = $request['pg_order_id'];
        //     $freedom = Freedom::find()->where(['pg_salt' => $request['pg_order_id']])->one();
        //     if ($freedom) {
        //         $order = Order::findOne($order_id);
        //         $order->status = Order::STATUS_PAID;
        //         $order->save(false);
        //     } else {
        //         $this->redirect('payment-failure');
        //     }

        // }
        return $this->render('thanks', []);
    }

    public function actionPaymentFailure()
    {
        return $this->render('failure', []);
    }

    public function actionFreedomState()
    {
        $req = Yii::$app->request;
        $post = $req->post();
        $get = $req->get();
        $dao = Yii::$app->db;
        $date = date('Y-m-d H:i');
        if ($post) {
            $dao->createCommand()->insert('test', ['title' => 'freedom sate post ' . $date, 'desc' => json_encode($post)])->execute();
        }
        if ($get) {
            $dao->createCommand()->insert('test', ['title' => 'freedom state get ' . $date, 'desc' => json_encode($get)])->execute();
        }
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }


}

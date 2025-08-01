<?php

namespace app\controllers;

use app\models\Notification;
use app\models\NotificationList;
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
use yii\helpers\Json;
use Exception;

class SiteController extends Controller
{
    public $layout;

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
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['user/signin']);
        } elseif (Yii::$app->user->identity->isAdmin) {
            return $this->redirect(['/admin']);
        } else {
            return $this->redirect(['/owner']);
        }

        //return $this->render('index');
    }

    public function actionShipment()
    {
        return $this->render('page', ['id' => 1]);
    }

    public function actionSearchRegions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Yii::$app->request->get('q', '');
        if (empty($query) || strlen($query) < 2) {
            return ['results' => []];
        }

        try {
            $client = Yii::$app->meili->connect();
            $health = $client->health();
            if ($health['status'] !== 'available') {
                throw new Exception('MeiliSearch server not available');
            }

            $index = $client->index('region');
            $searchResults = $index->search($query);

            $results = [];
            $hits = $searchResults->getHits();

            if (is_array($hits) && !empty($hits)) {
                foreach ($hits as $hit) {
                    if (!is_array($hit) || !isset($hit['id']) || !isset($hit['name'])) {
                        Yii::warning('Invalid hit structure: ' . Json::encode($hit), 'meilisearch');
                        continue;
                    }

                    $id = $hit['id'];
                    if (is_numeric($id)) {
                        $id = (string) $id;
                    } elseif (!is_string($id)) {
                        Yii::warning('Invalid ID type for hit: ' . Json::encode($hit), 'meilisearch');
                        continue;
                    }

                    $results[] = [
                        'id' => (string) $hit['id'],
                        'text' => $hit['name'], // this is what Select2 expects by default
                        'display' => $hit['name'] . (!empty($hit['region']) ? ' (' . $hit['region'] . ')' : ''),
                        'geo_data' => $hit['geo_data']

                    ];
                }
            }

            return ['results' => $results, 'total' => count($results)];

        } catch (Exception $e) {
            Yii::error('MeiliSearch error: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'meilisearch');

            return [
                'results' => [],
                'error' => 'Search temporarily unavailable',
                'debug' => YII_DEBUG ? $e->getMessage() : null
            ];
        }
    }

    public function actionAbout()
    {

        $query = Yii::$app->request->get('q');
        $client = Yii::$app->meili->connect();
        $results = $client->index('region')->search('');

        print_r($results->getHits());
        echo "</pre>";
    }


    public function actionPrivacy()
    {
        $this->layout = "general";
        return $this->render('page', ['id' => 1]);
    }

    public function actionReturn()
    {
        $this->layout = "general";
        return $this->render('page', ['id' => 2]);
    }

    public function actionOffer()
    {
        $this->layout = "general";
        return $this->render('page', ['id' => 3]);
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
            ->setFrom('send@dingo.kg')
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

    public function actionCreateNotification($type, $model_id = Notification::TYPE_BOOKING){
        $notification = new Notification();
        $voc = NotificationList::findOne($type);
        $notification->title = $voc->title;
        $notification->title_en = $voc->title_en;
        $notification->title_ky = $voc->title_ky;

        if($model_id == NotificationList::CATEGORY_OBJECT){
            
        }
    }

    public function actionAppleCredentials()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appIDs' => ['9QAR57XHB4.com.dingo.dingoapplication'],
                        'paths' => ['*'], // Use '*' to allow all paths
                        'components' => [
                            ['/' => '/']
                        ]
                    ]
                ]
            ],
            'webcredentials' => [
                'apps' => ['9QAR57XHB4.com.dingo.dingoapplication']
            ]
        ];
    }


}

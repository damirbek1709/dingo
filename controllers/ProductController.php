<?php

namespace app\controllers;

use app\models\Category;
use app\models\Order;
use app\models\ProductTag;
use app\models\Tag;
use Yii;
use app\models\Product;
use app\models\User;
use app\models\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\FileHelper;
use dektrium\user\filters\AccessRule;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\Cookie;
use app\models\OrderItem;
use app\models\Freedom;
use yii\httpclient\Client;
use rico\yii2images\models\Image;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'roleAccess' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'view',
                            'index',
                            'cart',
                            'add-cart',
                            'remove-cover',
                            'delete-from-cart',
                            'add-to-fav',
                            'favorites',
                            'cart-checkout',
                            'cart-amount-update',
                            'discount',
                            'accessories',
                            'test-query',
                            'revoke'
                        ],
                        'roles' => ['?', '@']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'admin', 'delete', 'remove-image'],
                        'roles' => ['admin']
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'uploadPhoto' => [
                'class' => 'app\models\UploadAction',
                'url' => Yii::$app->urlManager->createUrl('images/product/cover/temporary'),
                'path' => '@webroot/images/product/cover/temporary/',
                'width' => 400,
                'height' => 720,

            ]
        ];
    }

    // public function actionUploadPhoto(){
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     return "test";
    // }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex($id = null)
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $title = Yii::t('app', 'Товары');
        if ($id) {
            $dataProvider->query->andFilterWhere(['category_id' => $id]);
            $title = Category::findOne($id)->name;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title
        ]);
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionDiscount()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $title = Yii::t('app', 'Скидки');
        $dataProvider->query->andFilterWhere(['not', ['new_price' => [null, 0]]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title
        ]);
    }


    public function actionAddToFav()
    {
        $cookie_name = "fav";
        $arr = [];
        $id = Yii::$app->request->post('id');
        if (isset($_COOKIE[$cookie_name])) {
            $arr = unserialize($_COOKIE[$cookie_name]);
            if (in_array($id, $arr)) {
                unset($arr[$id]);
            } else {
                $arr[$id] = $id;
            }
        } else {
            $arr[$id] = $id;
        }

        $cookie_value = serialize($arr);
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
        return count($arr);
    }
    public function actionFavorites()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (isset($_COOKIE['fav'])) {
            $arr = unserialize($_COOKIE['fav']);
            $dataProvider->query->andFilterWhere(['id' => $arr]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => Yii::t('app', 'Избранные')
        ]);
    }

    public function actionAccessories()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $main_id = 2;
        $id_arr = ArrayHelper::map(Category::find()->where(['parent_id' => $main_id])->all(), 'id', 'id');
        $dataProvider->query->andFilterWhere(['id' => $id_arr]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => Yii::t('app', 'Аксессуары')
        ]);
    }


    public function actionAdmin()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->getView()->registerMetaTag(['property' => 'og:title', 'content' => $model->title]);
        $this->getView()->registerMetaTag(['property' => 'og:description', 'content' => $model->description]);
        $this->getView()->registerMetaTag(['property' => 'og:image', 'content' => Yii::$app->request->absoluteUrl . $model->getImage()->getUrl('200x350')]);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->tagNames) {
                foreach ($model->tagNames as $item) {
                    if (!Tag::find()->where(['name' => $item])->one()) {
                        $tag = new Tag();
                        $tag->name = $item;
                        $tag->save();
                    } else {
                        $tag = Tag::find()->where(['name' => $item])->one();
                    }
                    $product_tag = new ProductTag();
                    $product_tag->tag_id = $tag->id;
                    $product_tag->product_id = $model->id;
                    $product_tag->save();
                }
            }
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }
            if ($model->main_img_id) {
                $img = $this->getImageById($model->main_img_id);
                $model->setMainImage($img);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionRevoke($id)
    {
        $redirect_url = Freedom::revoke($id);
        return $this->redirect($redirect_url);
    }

    public function actionCartCheckout()
    {
        $order = new Order();
        $data = Yii::$app->request->cookies->getValue('cart', null);
        if ($data) {
            $data = unserialize($data);
            $sum = 0;
            foreach ($data as $item) {
                $sum += $item['price'] * $item['amount'];
            }
        }
        if ($order->load(Yii::$app->request->post())) {
            $order->status = Order::STATUS_PROCESSING;
            $order->sum = $sum;
            $order->created_at = date('Y-m-d H:i:s');
            if (!Yii::$app->user->isGuest) {
                $order->buyer_id = Yii::$app->user->id;
            }

            if ($order->save()) {
                $products = [];
                foreach ($data as $key => $val) {

                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $val['id'];
                    $orderItem->product_title = Product::findOne($val['id'])->title;
                    $orderItem->price = $val['price'] * $val['amount'];
                    $orderItem->amount = $val['amount'];
                    $orderItem->size = $val['size'];
                    $orderItem->save();

                    $products['id'] = Product::findOne($val['id'])->product_code;
                    $products['price'] = $orderItem->price;
                    $products['quantity'] = $orderItem->amount;
                }

                $bitrix_user_id = null;
                if (!Yii::$app->user->isGuest) {
                    $user = User::findOne(Yii::$app->user->id);
                    if ($user->bitrix_user_id) {
                        $bitrix_user_id = $user->bitrix_user_id;
                    }
                }

                $dataToSend = [
                    'userinfo' => [
                        'name' => $order->name,
                        'phone' => $order->phone,
                        'bitrix_user_id' => $bitrix_user_id,
                    ],
                    'orderinfo' => [
                        'order_id'=>$order->id,
                        'total' => $sum,
                        'currency' => 'KGS',
                        'pay_type' => 'by_card',
                        'products' => $products
                    ]
                ];

                $jsonData = $dataToSend;
                $this->sendJsonData($jsonData, 'https://node.dilbar.style/order/create');

                $redirect_url = Freedom::pay([
                    'default_sum' => $sum,
                    'currency' => 'KGS',
                    'order_id' => $order->id,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                ]);
                return $this->redirect($redirect_url);
            }
        }
        return $this->render('cart-check', [
            'order' => $order,
            'data' => $data,
            'cart_sum' => $sum
        ]);
    }

    private function sendJsonData($data, $url)
    {
        $client = new Client();

        $decoded = json_decode($this->getToken(), true);
        $token = $decoded['token'];

        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($url)
            ->setData($data)
            ->setHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])
            ->send();

        if ($response) {
            $decoded_response = json_decode($response->content,true);
            if (!Yii::$app->user->isGuest) {
                $user = User::findOne(Yii::$app->user->id);
                $user->bitrix_user_id = $decoded_response['userid'];
                $user->save(false);
            }
        }
    }

    public function getToken()
    {
        $client = new Client();
        $url = "https://node.dilbar.style/login";
        $data = [
            "username" => "admin",
            "password" => "d26b650cd3a0dc61a5a56"
        ];

        $request = $client->createRequest()
            ->setMethod('post')
            ->setUrl($url)
            ->setData($data)
            ->send();
        return $request->content;
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->tagNames) {
                ProductTag::deleteAll(['product_id' => $model->id]);
                foreach ($model->tagNames as $item) {
                    if (!Tag::find()->where(['name' => $item])->one()) {
                        $tag = new Tag();
                        $tag->name = $item;
                        $tag->save();
                    } else {
                        $tag = Tag::find()->where(['name' => $item])->one();
                    }
                    $product_tag = new ProductTag();
                    $product_tag->tag_id = $tag->id;
                    $product_tag->product_id = $model->id;
                    $product_tag->save();
                }
            }
            $model->images = UploadedFile::getInstances($model, 'images');
            if ($model->images) {
                foreach ($model->images as $image) {
                    $path = Yii::getAlias('@webroot/uploads/images/store/') . $image->name;
                    $image->saveAs($path);
                    $model->attachImage($path, true);
                    @unlink($path);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionTestQuery()
    {
        $data = Yii::$app->request->cookies->getValue('cart', null);
        $jsonData = json_encode($data);
        $response = $this->sendJsonData($jsonData, 'https://dilbar.style/api/order/add-order');
        return $response;
    }

    public function actionFreedomPay()
    {
        $post = Yii::$app->request->post();
        //var_dump($post);
        //exit();

        $redirect_url = Freedom::pay([
            'default_sum' => $post['default_sum'],
            'donation_sum' => $post['donation_sum'],
            'currency' => Yii::$app->request->post('currency'),
            'name' => Yii::$app->request->post('name'),
            'email' => Yii::$app->request->post('email'),
        ]);

        return $this->redirect($redirect_url);
    }


    public function actionCart()
    {
        return $this->render('cart');
    }

    public function actionCartAmountUpdate()
    {
        $id = Yii::$app->request->post('id');
        $amount = Yii::$app->request->post('amount');
        $cart = Yii::$app->request->cookies->getValue('cart', null);
        if ($cart) {
            $data = unserialize($cart, ["allowed_classes" => false]);
            $data[$id]['amount'] = $amount;
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new \yii\web\Cookie([
                'name' => 'cart',
                'value' => serialize($data),
            ]));
        }
    }

    public function actionRemoveImage($product_id, $image_id)
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        $image = Image::findOne($image_id)->delete();
        // $post = $this->findModel($product_id);
        // $post->removeImage($image_id);

    }

    public function actionAddCart()
    {
        $id = Yii::$app->request->post('id');
        $title = Yii::$app->request->post('title');
        $price = Yii::$app->request->post('price');
        $amount = Yii::$app->request->post('amount');
        $size = Yii::$app->request->post('size');

        $cookies = Yii::$app->response->cookies;


        $info = ['id' => $id, 'title' => $title, 'price' => $price, 'amount' => $amount, 'size' => $size];
        $data = [];

        $cart_count = 0;
        $cart = Yii::$app->request->cookies->getValue('cart', null);

        if ($cart) {
            $data = unserialize($cart);
            $cart_count = count($data) + $amount;
            if (array_key_exists($id, $data)) {
                $info = [
                    'id' => $id,
                    'title' => $title,
                    'price' => $price,
                    'amount' => $data[$id]['amount'] + $amount,
                    'size' => $size
                ];

                $data[$id] = $info;
            } else {
                $data[$id] = $info;
            }
        } else {
            $data[$id] = $info;
        }

        $cookies->add(new \yii\web\Cookie([
            'name' => 'cart',
            'value' => serialize($data),
            'httpOnly' => false,
            // Check if this is set
        ]));
        return $cart_count;
    }

    public function actionDeleteFromCart()
    {
        $id = Yii::$app->request->post('id');
        $data = [];
        $cart = Yii::$app->request->cookies->getValue('cart');
        if ($cart) {
            $data = unserialize($cart);
            if (array_key_exists($id, $data)) {
                unset($data[$id]);
            }
        }
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie([
            'name' => 'cart',
            'value' => serialize($data),
        ]));
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['admin']);
    }

    public function actionRemoveCover()
    {
        $id = Yii::$app->request->post('id');
        FileHelper::removeDirectory(Yii::getAlias("@webroot/images/product/cover/{$id}"));
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

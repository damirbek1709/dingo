<?php

namespace app\modules\api\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use DateTime;
use DateInterval;

class ObjectController extends BaseController
{
    public $modelClass = 'app\models\Object';

    /**
     * @inheritDoc
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['view'], $actions['create'], $actions['update'], $actions['delete'], $actions['options']);

        $actions['index'] = [
            'class' => 'yii\rest\IndexAction',
            'modelClass' => $this->modelClass,
            'prepareDataProvider' => function () {

                $result = [];

                $paidServices = PaidService::find()->sorted()->all();

                foreach ($paidServices as $service) {
                    $searchModel = new PostSearch();
                    $searchModel->status = Post::STATUS_ACTIVATED;

                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true, false, $service->id);

                    $result[$service->title] = $dataProvider;
                }

                $searchModel = new PostSearch();
                $searchModel->status = Post::STATUS_ACTIVATED;

                $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true, false, null);
                $dataProvider->sort->defaultOrder = ['upped_at' => SORT_DESC];
                $dataProvider->pagination->pageSize = 20;

                $result['all'] = $dataProvider;

                return $result;
            },
        ];

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['except'] = [
            'index',
            'recommended-price',
            'view',
            'ratings',
            'save-search-guest',
            'cities',
            'rating-count-grades'
        ];


        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'recommended-price', 'view', 'ratings', 'save-search-guest', 'cities', 'rating-count-grades', 'set-discount', 'test-discount'],
                    'roles' => ['?', '@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['own', 'remove-rate', 'favorites', 'add-to-favorites', 'remove-from-favorites', 'save-search', 'searches', 'delete-search', 'up-schedule', 'up-schedule-new'],
                    'roles' => ['user'],
                ],
                [
                    'allow' => true,
                    'roles' => ['createPost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['edit'],
                    'roles' => ['updatePost'],
                    'roleParams' => function () {
                        return ['post' => Post::findOne(['id' => Yii::$app->request->get('id')])];
                    },
                ],
                [
                    'allow' => true,
                    'actions' => ['activate', 'deactivate', 'up'],
                    'roles' => ['updatePost'],
                    'roleParams' => function () {
                        return ['post' => Post::findOne(['id' => Yii::$app->request->post('id')])];
                    },
                ],
                [
                    'allow' => true,
                    'actions' => ['remove-images', 'remove-image', 'set-image-as-main'],
                    'roles' => ['updatePost'],
                    'roleParams' => function () {
                        return ['post' => Post::findOne(['id' => Yii::$app->request->post('post_id')])];
                    },
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'add' => ['POST'],
                'edit' => ['POST'],
                'remove' => ['POST'],
                'activate' => ['POST'],
                'deactivate' => ['POST'],
                'up' => ['POST'],
                'remove-images' => ['POST'],
                'remove-image' => ['POST'],
                'set-image-as-main' => ['POST'],
                'upnote' => ['POST'],
                'up-schedule-new' => ['POST'],
                'delete-search' => ['POST'],
                'add-to-favorites' => ['POST'],
                'remove-from-favorites' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->tags = $model->tags ? unserialize($model->tags) : null;
        $model->views_count += 1;
        if ($model->updateAttributes(['views_count'])) {
            $client = Yii::$app->meili->connect();
            $client->index('posts')->updateDocuments(['id' => $model->id ? $model->id : null, 'views_count' => $model->views_count]);
            return $model;
        }
        //$model->addView();

        return $model;
    }

    /**
     * List own posts.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionOwn()
    {
        $categories = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'category_id');
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true, null);
        //$dataProvider->query->andFilterWhere(['in', 'cat.id', $$categories]);
        // Get all models from the dataProvider
        $models = $dataProvider->getModels();

        // Unserialize the 'tags' field for each model
        foreach ($models as &$model) {
            if (isset($model->tags) && is_string($model->tags)) {
                $model->tags = unserialize($model->tags);
            }
        }

        // Update the models in the dataProvider
        $dataProvider->setModels($models);

        return $dataProvider;
    }

    /**
     * List favorites posts.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionFavorites()
    {
        $identity = Yii::$app->user->identity;
        return $identity->favoritePosts;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionAddToFavorites()
    {
        $response["success"] = false;
        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $user_id = Yii::$app->user->id;
        $favorite = new Favorite();

        $favorite->user_id = $user_id;
        $favorite->post_id = $post_id;

        if ($favorite->save()) {
            $response["success"] = true;
            $response["message"] = 'Объявление добавлено в избранное.';
            $response["favorite"] = $favorite;
        } else {
            $response["errors"] = $favorite->errors;
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveFromFavorites()
    {
        $response["success"] = false;
        if (Yii::$app->request->post('post_id')) {
            $post_id = Yii::$app->request->post('post_id');
        } else {
            $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        }

        $favorite = $this->findFavorite($post_id);

        if ($favorite->delete()) {
            $response["success"] = true;
            $response["message"] = 'Объявление удалено из избранного.';
        } else {
            $response["errors"] = 'Не удалось удалить из избранного.';
        }

        return $response;
    }

    /**
     * Minimum-Maximum price.
     * @return mixed
     */
    public function actionRecommendedPrice()
    {
        $minimum_usd = null;
        $minimum_kgs = null;
        $maximum_usd = null;
        $maximum_kgs = null;

        $searchModel = new PostPriceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->sort->defaultOrder = [
            'price_usd' => SORT_ASC,
            'price_kgs' => SORT_ASC,
        ];

        $minimum = $dataProvider->query->one();

        $dataProvider->sort->defaultOrder = [
            'price_usd' => SORT_DESC,
            'price_kgs' => SORT_DESC,
        ];

        $maximum = $dataProvider->query->one();

        $result = [
            'minimum_usd' => $minimum !== null ? $minimum->price_usd : null,
            'minimum_kgs' => $minimum !== null ? $minimum->price_kgs : null,
            'maximum_usd' => $maximum !== null ? $maximum->price_usd : null,
            'maximum_kgs' => $maximum !== null ? $maximum->price_kgs : null,
        ];

        return $result;
    }

    /**
     * Add a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAdd($category_id)
    {
        $category = $this->findCategory($category_id);

        $response["success"] = false;

        $model = new Post();
        $model->category_ids = $category->alternativesIds;
        $model->user_id = Yii::$app->user->id;
        $user_id = Yii::$app->user->id;
        $categories = Category::findAll($category->alternativesIds);
        $dynamicAttributes = $category->dynamicAttributes;

        $dynamicModel = Post::prepareDynamicModel($model, $dynamicAttributes);

        $dateTime = new DateTime();
        $dateTime->modify('+1 week');

        $model->active_until = $dateTime->format('Y-m-d');

        $model->moderation_status = Post::MODERATION_STATUS_APPROVED;

        foreach ($categories as $category) {
            if (isset($category->is_moderation_needed) && $category->is_moderation_needed === Category::IS_MODERATION_NEEDED) {
                $model->moderation_status = Post::MODERATION_STATUS_PENDING;
                $model->status = Post::STATUS_DEACTIVATED;
            }

            if ($category->post_limit > 0 && $category->isEndpoint) {
                $postsCount = Post::find()->joinWith(['categories as cat'])->where(['user_id' => Yii::$app->user->id, 'cat.id' => $category->id])->count();

                if ($postsCount >= $category->post_limit) {
                    $model->addErrors(['Лимит объявлений в данной категории - ' . $category->post_limit]);
                    $response["errors"] = $model->errors;

                    return $response;
                }
            }
        }

        if ($model->load(Yii::$app->request->bodyParams, '') && $model->validate() && $dynamicModel->load(Yii::$app->request->bodyParams, '') && $dynamicModel->validate()) {
            $model->tags = $model->tags ? serialize($model->tags) : null;
            $model->size = $model->size ? serialize($model->size) : null;
            $model->color = $model->color ? serialize($model->color) : null;
            $saveDataResult = Post::saveData($model, $dynamicModel, $dynamicAttributes, $user_id, $categories, true, true);

            $saved = ArrayHelper::getValue($saveDataResult, 'saved');
            $model = ArrayHelper::getValue($saveDataResult, 'model');
            $dynamicModel = ArrayHelper::getValue($saveDataResult, 'dynamicModel');
            $errors = ArrayHelper::getValue($saveDataResult, 'errors');

            if ($saved) {
                $response["success"] = true;
                $response["message"] = 'Объявление добавлено.';
                $model->tags = $model->tags ? unserialize($model->tags) : null;
                $response["post_id"] = (int) $model->id;
            } else {
                $response["errors"] = [];
                $response["errors"] = ArrayHelper::merge($response["errors"], $model->errors);
                $response["errors"] = ArrayHelper::merge($response["errors"], $dynamicModel->errors);
                $response["errors"] = ArrayHelper::merge($response["errors"], $errors);
            }
        } else {
            $response["errors"] = [];
            $response["errors"] = ArrayHelper::merge($response["errors"], $model->errors);
            $response["errors"] = ArrayHelper::merge($response["errors"], $dynamicModel->errors);
        }

        return $response;
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEdit()
    {
        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');
        $model = $this->findModel($id);

        $user_id = $model->user_id;
        $categories = Category::findAll($model->mainCategory->alternativesIds);
        $dynamicAttributes = $model->dynamicAttributes;

        $response["success"] = false;

        $dynamicModel = Post::prepareDynamicModel($model, $dynamicAttributes);

        foreach ($categories as $category) {
            if ($category->is_moderation_needed === Category::IS_MODERATION_NEEDED) {
                $model->moderation_status = Post::MODERATION_STATUS_PENDING;
            }
        }


        if ($model->load(Yii::$app->request->bodyParams, '') && $model->validate() && $dynamicModel->load(Yii::$app->request->bodyParams, '') && $dynamicModel->validate()) {

            $saveDataResult = Post::saveData($model, $dynamicModel, $dynamicAttributes, $user_id, $categories, true);

            $saved = ArrayHelper::getValue($saveDataResult, 'saved');
            $model = ArrayHelper::getValue($saveDataResult, 'model');
            $dynamicModel = ArrayHelper::getValue($saveDataResult, 'dynamicModel');
            $errors = ArrayHelper::getValue($saveDataResult, 'errors');

            if ($saved) {
                $response["success"] = true;
                $response["message"] = 'Объявление изменено.';
                $response["post"] = $model;
            } else {
                $response["errors"] = [];
                $response["errors"] = ArrayHelper::merge($response["errors"], $model->errors);
                $response["errors"] = ArrayHelper::merge($response["errors"], $dynamicModel->errors);
                $response["errors"] = ArrayHelper::merge($response["errors"], $errors);
            }
        } else {
            $response["errors"] = [];
            $response["errors"] = ArrayHelper::merge($response["errors"], $model->errors);
            $response["errors"] = ArrayHelper::merge($response["errors"], $dynamicModel->errors);
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionActivate()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');

        $model = $this->findModel($id);

        if ($model->activate()) {
            $response["success"] = true;
            $response["message"] = 'Объявление активировано.';
        } else {
            $response["errors"] = 'Не удалось активировать объявление';
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeactivate()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');
        $model = $this->findModel($id);

        if ($model->deactivate()) {
            $response["success"] = true;
            $response["message"] = 'Объявление деактивировано.';
        } else {
            $response["errors"] = 'Не удалось деактивировать объявление';
        }

        return $response;
    }

    /**
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUp()
    {
        $response["success"] = false;

        $id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'id');

        $model = $this->findModel($id);

        if ($model->up()) {
            $response["success"] = true;
            $response["message"] = 'Объявление поднято.';
        } else {
            $response["errors"] = 'Поднимать объявление возможно через какждые 3 часа';
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRemoveImages()
    {
        $response = [];
        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $images = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'images');

        $post = $this->findModel($post_id);

        foreach ($images as $item) {
            $mainImageId = $post->getImage()->id;

            foreach ($post->getImages() as $image) {
                if ($image->id == $item) {
                    $post->removeImage($image);
                    $response[$item]["item"] = $item;
                    $response[$item]["success"] = true;
                }
            }

            if ($mainImageId == $item) {
                foreach ($post->getImages() as $image) {
                    if (!is_a($image, PlaceHolder::className())) {
                        $post->setMainImage($image);
                        break;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRemoveImage()
    {
        $response["success"] = false;

        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $image_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'image_id');

        $post = $this->findModel($post_id);
        $mainImageId = $post->getImage()->id;

        foreach ($post->getImages() as $image) {
            if ($image->id == $image_id) {
                $post->removeImage($image);
                $response["success"] = true;
                $response["message"] = 'Изображение удалено.';
                $response["post"] = $post;
            }
        }

        if ($mainImageId == $image_id) {
            foreach ($post->getImages() as $image) {
                if (!is_a($image, PlaceHolder::className())) {
                    $post->setMainImage($image);
                    break;
                }
            }
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionSetImageAsMain()
    {
        $response["success"] = false;

        $post_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post_id');
        $image_id = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'image_id');

        $uploadedMainImage = UploadedFile::getInstanceByName('newmainimage');
        $post = $this->findModel($post_id);

        if ($uploadedMainImage) {
            $path = Yii::getAlias('@webroot/uploads/images/store/') . time() . '.' . $uploadedMainImage->extension;
            $uploadedMainImage->saveAs($path);
            $response["asdf"] = $post->attachImage($path, true);
            @unlink($path);
            $response["success"] = true;
            $response["message"] = 'Изображение сделано главным.';
            $response["post"] = $post;
        } else if ($image_id) {
            foreach ($post->getImages() as $image) {
                if ($image->id == $image_id) {
                    if (!is_a($image, PlaceHolder::className())) {
                        $post->setMainImage($image);
                        $response["success"] = true;
                        $response["message"] = 'Изображение сделано главным.';
                        $response["post"] = $post;
                    }
                }
            }
            if (!isset($response['message'])) {
                $response["message"] = 'image_id не найден в имеющих фото';
            }
        } else {
            $response["message"] = 'отсутствует newmainimage или image_id';
        }

        return $response;
    }

    public function actionUpnote()
    {
        $req = Yii::$app->request->post();
        $uid = Yii::$app->user->id;
        if ($uid && isset($req['note'])) {
            if (!empty($req['id'])) {
                $model = Note::find()->where(['id' => $req['id'], 'user_id' => $uid])->one();
                if ($model && empty($req['note'])) {
                    $model->delete();
                    return true;
                }
                if (!$model) {
                    return null;
                }
            } else if (!empty($req['post_id'])) {
                $model = Note::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();
                if (!$model) {
                    $model = new Note();
                    $model->user_id = $uid;
                    $model->post_id = $req['post_id'];
                }
            }
            if ($model) {
                $model->note = $req['note'];
                $model->save();
                return $model;
            }
        }
    }

    public function actionNote()
    {
        $req = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        if ($uid && !empty($req['post_id'])) {
            $dao = Yii::$app->db;
            //$note = $dao->createCommand("SELECT * FROM `note` WHERE post_id={$req['post_id']} AND user_id={$uid}")->queryOne();
            $note = Note::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();
            if ($note) {
                return $note;
            }
        }
        return null;
    }

    public function actionRemoveRate()
    {
        $req = Yii::$app->request->post();
        $response["success"] = false;

        $model = Rating::find()->where(['id' => $req['id']])->one();
        if ($model) {
            if ($model->delete()) {
                $response["success"] = true;
                $response["message"] = "Отзыв удален";
            }
        }
        return $response;
    }

    public function actionUprate()
    {
        $req = Yii::$app->request->post();
        $uid = Yii::$app->user->id;
        if ($uid && !empty($req['post_id']) && isset($req['rate'])) {
            if (!empty($req['id'])) {
                $model = Rating::find()->where(['id' => $req['id'], 'user_id' => $uid])->one();
                if (!$model) {
                    return null;
                }
            } else {
                $model = Rating::find()->where(['post_id' => $req['post_id'], 'user_id' => $uid])->one();

                if (!$model) {
                    $model = new Rating();
                    $model->user_id = $uid;
                    $model->post_id = $req['post_id'];
                }
            }

            $receiver_id = Post::findOne($req['post_id'])->user_id;
            $model->receiver_id = $receiver_id;
            $model->note = isset($req['note']) ? $req['note'] : '';
            $model->rate = (int) $req['rate'];
            $model->full_name = $req['name'];

            if ($model->save()) {
                $app_id = 0;
                $text = "Добавлен комментарий к вашему посту";
                $data = [
                    'id' => (string) $req['post_id'],
                    'text' => (string) $text
                ];

                $params = [
                    'notif' => ['title' => 'Внимание', 'body' => $text],
                    'data' => $data,
                ];

                $token_rows = Yii::$app->db->createCommand("SELECT * FROM fcm_token WHERE user_id='{$receiver_id}'")->queryAll();
                if ($token_rows) {
                    $arr = [];
                    foreach ($token_rows as $item) {
                        $token = (string) $item['token'];
                        Yii::$app->firebase->sendPushNotification($token, $params['title'], $params['body'], $data);
                    }
                }
            }
            return $model;
        }
    }

    public function actionRatings()
    {
        $req = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        if (!empty($req['post_id'])) {
            return Rating::find()->where(['post_id' => $req['post_id']])->all();
        }
        return [];
    }

    public function actionUpSchedule()
    {
        $user = Yii::$app->user->identity;
        $response["success"] = false;
        $params = Yii::$app->request->bodyParams;
        $schedule = new UpSchedule();
        $schedule->total_price = $params['total_price'] ? $params['total_price'] : null;
        $schedule->time_from = $params['time_from'] ? $params['time_from'] : null;
        $schedule->time_to = $params['time_to'] ? $params['time_to'] : null;
        $schedule->frequency = $params['frequency'] ? $params['frequency'] : null;
        $schedule->posts = serialize($params['post']);
        $schedule->type = $params['type'] ? $params['type'] : null;
        $schedule->created_at = date('Y-m-d H:i:s');
        $begin_day = null;
        $end_day = null;

        if ($params['type'] == UpSchedule::TYPE_MULTIPLE) {
            $schedule->days_amount = $params['days_amount'];
            $currentDateTime = new DateTime();
            $currentDateTime->add(new DateInterval("P{$params['days_amount']}D"));
            $schedule->date_to = $currentDateTime->format('Y-m-d H:i:s');

            if (date('H', strtotime($schedule->time_from)) <= date('H')) {
                $concat_begin_day = date('Y-m-d') . ' ' . $schedule->time_from;
                $timestamp = strtotime($concat_begin_day);
                $new_timestamp = $timestamp + 86400;
                $begin_day = date('Y-m-d', $new_timestamp);
            } else {
                $begin_day = date('Y-m-d');
            }
            $response["diapazon"] = "Кол-во дней: " . $params['days_amount'] . ". " . date('d.m.Y', strtotime($begin_day)) . " - " . date('d.m.Y', strtotime($begin_day . ' +' . $params['days_amount'] . ' days'));
            $response["type"] = UpSchedule::TYPE_MULTIPLE;
        } else {
            if (date('H', strtotime($schedule->time_from)) <= date('H')) {
                $concat_begin_day = date('Y-m-d') . ' ' . $schedule->time_from;
                $timestamp = strtotime($concat_begin_day);
                $new_timestamp = $timestamp + 86400;
                $schedule->date_to = date('Y-m-d H:i', $new_timestamp);
            } else {
                $schedule->date_to = $concat_begin_day = date('Y-m-d') . ' ' . $schedule->time_from;
            }
            $schedule->days_amount = null;
            $response["diapazon"] = date('d.m.Y H:i', strtotime($schedule->date_to));
            $response["type"] = UpSchedule::TYPE_SINGLE;
        }

        if ($user->kgsWallet === null || $params['total_price'] > $user->kgsWallet->balance) {
            $response["errors"][] = 'Недостаточно средств на балансе. Пополните кошелёк.';
        }

        if (empty($response["errors"])) {
            if ($schedule->save()) {
                Yii::$app->balanceManager->decrease(
                    [
                        'user_id' => $user->id,
                        'type' => Wallet::TYPE_KGS,
                    ],
                    $params['total_price'],
                    [
                        'type' => WalletTransaction::TYPE_PAID_SERVICE,
                        'multipleUp' => $user->id,
                    ]
                );
                $response["success"] = true;

                //$response["begin_date"] = $begin_day;
                //$response["end_date"] = date('Y-m-d', strtotime($begin_day . ' +' . $params['days_amount'] . ' days'));
                $response["paid_time"] = date('H:i', strtotime($schedule->created_at));
            } else {
                return $schedule->errors;
            }
        } else {
            return $response["errors"];
        }
        return $response;
    }


    public function actionUpScheduleNew()
    {
        $user = Yii::$app->user->identity;
        $response["success"] = false;
        $params = Yii::$app->request->bodyParams;
        $schedule = new UpScheduleNew();
        $schedule->total_price = $params['total_price'] ? $params['total_price'] : null;
        $schedule->post_list = $params['post'] ? serialize($params['post']) : null;
        $schedule->time_list = $params['time'] ? serialize($params['time']) : null;
        $schedule->date_from = date('Y-m-d', strtotime($params['date_from']));
        $schedule->date_to = date('Y-m-d', strtotime($params['date_to']));

        if ($user->kgsWallet === null || $params['total_price'] > $user->kgsWallet->balance) {
            $response["errors"][] = 'Недостаточно средств на балансе. Пополните кошелёк.';
        }

        if (empty($response["errors"])) {
            if ($schedule->save()) {
                Yii::$app->balanceManager->decrease(
                    [
                        'user_id' => $user->id,
                        'type' => Wallet::TYPE_KGS,
                    ],
                    $params['total_price'],
                    [
                        'type' => WalletTransaction::TYPE_PAID_SERVICE,
                        'multipleUp' => $user->id,
                    ]
                );
                $response["success"] = true;
                $response["paid_time"] = date('d-m-Y H:i');
            } else {
                return $schedule->errors;
            }
        } else {
            return $response["errors"];
        }
        return $response;
    }

    public function actionRatingCountGrades()
    {
        $req = Yii::$app->request->get();
        if (!empty($req['post_id'])) {
            $rate_arr = [];
            for ($i = 1; $i <= 5; $i++) {
                $rate_arr[$i] = (int) Rating::find()->where(['post_id' => $req['post_id'], 'rate' => $i])->count();
            }
            $all_count = (int) Rating::find()->where(['post_id' => $req['post_id']])->count();
            $rate_arr['all'] = $all_count;
            return $rate_arr;
        }
        return [];
    }

    protected static function dict($lang)
    {
        $dict = [
            'ru' => [
                'cond' => 'Состояние',
                'new' => 'Новый',
                'used' => 'б/у',
                'from_pre' => 'от',
                'from_su' => '',
                'to_pre' => 'до',
                'to_su' => '',
            ],
            'ky' => [
                'cond' => 'Абалы',
                'new' => 'Жаңы',
                'used' => 'б/у',
                'from_pre' => 'от',
                'from_su' => '',
                'to_pre' => 'до',
                'to_su' => '',
            ]
        ];
        return $dict[$lang];
    }

    protected static function attr()
    {
        $dao = Yii::$app->db;
        $rows = $dao->createCommand("SELECT id,title,`type` FROM `category_attribute`")->queryAll();
        return ['titles' => ArrayHelper::map($rows, 'id', 'title'), 'types' => ArrayHelper::map($rows, 'id', 'type')];
    }

    protected static function options()
    {
        $dao = Yii::$app->db;
        $rows = $dao->createCommand("SELECT id,`value` FROM `directory_option`")->queryAll();
        return ArrayHelper::map($rows, 'id', 'value');
    }

    /* protected static function ctgTitles()
    {
        $dao = Yii::$app->db;
        return $dao->createCommand("SELECT id,title,title_ky FROM `category`")->queryAll();
    } */

    public function actionSaveSearch()
    {
        $uid = Yii::$app->user->id;
        $qp = Yii::$app->request->queryParams;
        $qs = Yii::$app->request->queryString;
        /* if (isset($qp['PostSearch'])) {
            $json = json_encode($qp['PostSearch']);
        } else {
            $json = json_encode($qp);
        } */

        if (!empty($qp['PostSearch']['ctg_ids'])) {
            $ctg_id = $qp['PostSearch']['ctg_ids'][0];
            $qp['categoriesChain'] = Post::getCategoriesChainStatic($ctg_id);
            if (($key = array_search($ctg_id, $qp['categoriesChain'])) !== false) {
                unset($qp['categoriesChain'][$key]);
            }
        }

        $json = json_encode($qp);
        $td = self::titleDesc($qp);
        $model = new SavedSearch();
        $model->title = $td['title'];
        $model->description = $td['desc'];
        $model->paramstr = $qs;
        $model->paramjsn = $json;
        $model->user_id = $uid;
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
        return null;
    }

    public function actionSearches()
    {
        $uid = Yii::$app->user->id;
        return SavedSearch::find()->where(['user_id' => $uid])->all();
    }

    public function actionDeleteSearch()
    {
        $id = Yii::$app->request->post('id');
        $uid = Yii::$app->user->id;
        $isDel = SavedSearch::find()->where(['user_id' => $uid, 'id' => $id])->one()->delete();
        if ($isDel) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    public function actionSetDiscount()
    {
        $response["success"] = false;
        $items = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'post');
        $client = Yii::$app->meili->connect();
        foreach ($items as $val) {
            $post = Post::findOne($val['id']);
            $post->discount = $val['discount'];
            if ($post->update()) {
                $response['success'] = true;
                $arr = ['id' => $val['id'], 'discount' => $val['discount']];
                $client->index('posts')->updateDocuments($arr);
            } else {
                $response['success'] = false;
            }
        }
        return $response;
    }

    public function actionTestDiscount()
    {
        $response["success"] = false;
        $items = ArrayHelper::getValue(Yii::$app->request->bodyParams, 'order');
        echo "<pre>";
        print_r($items);
        echo "</pre>";
        die();
    }


    public function actionSaveSearchGuest()
    {
        $qp = Yii::$app->request->queryParams;
        $qs = Yii::$app->request->queryString;
        /* if (isset($qp['PostSearch'])) {
            $json = json_encode($qp['PostSearch']);
        } else {
            $json = json_encode($qp);
        } */
        if (!empty($qp['PostSearch']['ctg_ids'])) {
            $ctg_id = $qp['PostSearch']['ctg_ids'][0];
            $qp['categoriesChain'] = Post::getCategoriesChainStatic($ctg_id);
            if (($key = array_search($ctg_id, $qp['categoriesChain'])) !== false) {
                unset($qp['categoriesChain'][$key]);
            }
        }
        $json = json_encode($qp);
        $td = self::titleDesc($qp);
        if (!empty($td['title'])) {
            return ['title' => $td['title'], 'description' => $td['desc'], 'paramstr' => $qs, 'paramjsn' => $json];
        } else {
            return null;
        }
    }


    protected static function titleDesc($qp)
    {
        $title = '';
        $desc = [];
        $lang = 'ru';
        if (isset($qp['lang'])) {
            $lang = $qp['lang'];
        }
        $dict = self::dict($lang);
        $attrTb = self::attr();
        $options = self::options();
        $attrLbls = $attrTb['titles'];
        $attrType = $attrTb['types'];

        $dao = Yii::$app->db;
        $prices = [];
        $attribs = [];
        $currency = '';
        if (isset($qp['keyword'])) {
            $desc[] = $qp['keyword'];
        }
        if (isset($qp['PostSearch'])) {
            foreach ($qp['PostSearch'] as $k => $v) {
                if ($k == 'ctg_ids') {
                    foreach ($v as $ctg_id) {
                        $ctg = $dao->createCommand("SELECT title, title_ky FROM `category` WHERE id={$ctg_id}")->queryOne();
                        $title = $ctg['title'] . ' ';
                    }
                } else if ($k == 'category_id') {
                    $ctg = $dao->createCommand("SELECT title, title_ky FROM `category` WHERE id={$v}")->queryOne();
                    $title = $ctg['title'];
                } else if ($k == 'condition') {
                    if ($v == Post::CONDITION_NEW) {
                        $desc[] = $dict['cond'] . ': ' . $dict['new'];
                    } else {
                        $desc[] = $dict['cond'] . ': ' . $dict['used'];
                    }
                } else if ($k == 'keyword') {
                    $desc[] = $v;
                } else if ($k == 'price_kgs_from') {
                    $prices[0] = $v;
                    $currency = 'сом';
                } else if ($k == 'price_kgs_to') {
                    if (!isset($prices[0])) {
                        $prices[0] = 0;
                    }
                    $prices[1] = $v;
                    $currency = 'сом';
                } else if ($k == 'price_usd_from') {
                    $prices[0] = $v;
                    $currency = 'дол';
                } else if ($k == 'price_usd_to') {
                    if (!isset($prices[0])) {
                        $prices[0] = '0';
                    }
                    $prices[1] = $v;
                    $currency = 'дол';
                } else {
                    $ex = explode('_', $k);
                    if (isset($attrType[$ex[0]])) {
                        if ($attrType[$ex[0]] == 'directory_multiple') {
                            $opts = [];
                            foreach ($v as $opt) {
                                $opts[] = $options[$opt];
                            }
                            $desc[] = $attrLbls[$k] . ': ' . implode(', ', $opts);
                        } else if ($attrType[$ex[0]] == 'directory') {
                            $desc[] = $attrLbls[$k] . ': ' . $options[$v];
                        } else {
                            $attribs[$ex[0]][] = $v;
                        }
                    }
                }
            }
            foreach ($attribs as $ak => $av) {
                $desc[] = $attrLbls[$ak] . ': ' . implode('-', $av);
            }
            if ($prices) {
                if (count($prices) == 2) {
                    $desc[] = implode('-', $prices) . ' ' . $currency;
                } else {
                    $desc[] = $prices[0] . '+ ' . $currency;
                }
            }
        }

        return ['title' => $title, 'desc' => implode(', ', $desc)];
    }

    /**
     * Finds the Favorite model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $post_id
     * @return Favorite the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findFavorite($post_id)
    {
        if (($model = Favorite::find()->where(['user_id' => Yii::$app->user->id, 'post_id' => $post_id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Избранное не найдено.');
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCategory($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Категория не найдена.');
    }

    public function actionCities()
    {
        return City::find()->orderBy('priortet ASC')->all();
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Post::findOne($id);
        if ($model) {
            if ($model->moderation_status === Post::MODERATION_STATUS_APPROVED) {
                return $model;
            } elseif ($model->user_id == Yii::$app->user->id) {
                return $model;
            } else {
                if (Yii::$app->user->can('updatePost', ['post' => $model])) {
                    return $model;
                }
            }
            return $model;
        } else {

            throw new NotFoundHttpException('Объявление не найдено.');
        }
    }
}

<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use rico\yii2images\models\Image;
use app\models\user\User;


class Objects extends \yii\db\ActiveRecord
{

    const STATUS_NOT_PUBLISHED = 0;
    const STATUS_READY_FOR_PUBLISH = 1;
    const STATUS_ON_MODERATION = 2;
    const STATUS_PUBLISHED = 3;
    const STATUS_DENIED = 4;
    const STATUS_DELETED = 5;

    public $user_id;
    public $type;
    public $name;
    public $name_en;
    public $name_ky;
    public $city_en;
    public $city_ky;
    public $city;
    public $city_id;
    public $address;
    public $address_ky;
    public $address_en;
    public $currency;
    public $features;
    public $phone;
    public $site;
    public $check_in;
    public $check_out;
    public $object;
    public $reception;
    public $description;
    public $description_en;
    public $description_ky;
    public $payment;
    public $lat;
    public $lon;
    public $email;
    public $meal_terms;
    public $rooms;
    public $general_room_count;
    public $terms;
    public $early_check_in;
    public $late_check_in;
    public $internet_public = false;
    public $animals_allowed = false;
    public $meal_purchaise = false;
    public $meal_type = false;
    public $meal_cost = false;
    public $uploadImages;
    public $images;
    public $img;
    public $children;
    public $deny_reason;
    public $guest_amount;

    public $status;
    public $ceo_doc;
    public $financial_doc;
    public $from_price;
    public $phone_code;

    public $host_name;



    const SEARCH_TYPE_CITY = 3;
    const SEARCH_TYPE_REGION = 1;
    const SEARCH_TYPE_HOTEL = 2;

    const CURRENCY_KGS = 1;
    const CURRENCY_USD = 2;

    public $comfort_list;

    const TERM_MEAL_BREAKFEST = 1;
    const TERM_MEAL_THREE_TIMES = 2;

    const OBJECT_TYPE_APARTHOTEL = 1;
    const OBJECT_TYPE_APARTMENTS = 2;
    const OBJECT_TYPE_RESTBASE = 3;
    const OBJECT_TYPE_BUNGALOW = 4;
    const OBJECT_TYPE_BOUTIQUE_HOTEL = 5;
    const OBJECT_TYPE_VILLA = 6;
    const OBJECT_TYPE_GLAMPING = 7;
    const OBJECT_TYPE_GUESTHOUSE = 8;
    const OBJECT_TYPE_RESIDENTIAL_PREMISES = 9;
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => Yii::t('app', 'Тип'),
            'city' => Yii::t('app', 'Город'),
            'address' => Yii::t('app', 'Адрес'),
            'currency' => Yii::t('app', 'Валюта'),
            'features' => Yii::t('app', 'Адрес'),
            'site' => Yii::t('app', 'Сайт'),
            'check_in' => Yii::t('app', 'Заезд'),
            'check_out' => Yii::t('app', 'Выезд'),
            'reception' => Yii::t('app', 'Ресепшн'),
            'description' => Yii::t('app', 'Описание'),
            'images' => Yii::t('app', 'Фотографии'),
            'lat' => 'Latitude',
            'lon' => 'Longitude',
            'email' => 'E-mail',
            'phone' => Yii::t('app', 'Контакты'),
            'name' => Yii::t('app', 'Название'),
            'name_en' => Yii::t('app', 'Название на английском'),
            'name_ky' => Yii::t('app', 'Название на кыргызском'),

            'city_en' => Yii::t('app', 'Город на английском'),
            'city_ky' => Yii::t('app', 'Город на кыргызском'),
            'city_id' => Yii::t('app', 'Населенный пункт'),

            'address_en' => Yii::t('app', 'Адрес на английском'),
            'address_ky' => Yii::t('app', 'Адрес на кыргызском'),

            'description_en' => Yii::t('app', 'Описание на английском'),
            'description_ky' => Yii::t('app', 'Описание на кыргызском'),
            'general_room_count' => Yii::t('app', 'Общее количество комнат'),
            'host_name' => Yii::t('app', 'Имя хоста'),
        ];
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    public static function hostName($user_id)
    {
        $user = User::findOne($user_id);
        return $user? $user->name : "Имя не заполнено";
    }

    public static function attributeIndexed($attr)
    {
        $lang_index = 0;
        switch (Yii::$app->language) {
            case 'ru':
                $lang_index = 0;
                break;
            case 'en':
                $lang_index = 1;
                break;
            case 'ky':
                $lang_index = 2;
                break;
            default:
                $lang_index = 0;
        }
        return $attr[$lang_index];
    }

    public function rules()
    {
        return [
            [
                [
                    'type',
                    'address',
                    'address_en',
                    'address_ky',
                    'phone',
                    'check_in',
                    'check_out',
                    'description',
                    'description_en',
                    'description_ky',
                    'email',
                    'name',
                    'name_en',
                    'name_ky',
                    'city_id'
                ],
                'required'
            ],
            //['phone', 'match', 'pattern' => '/^\+996\s\d{3}\s\d{2}\s\d{2}\s\d{2}$/', 'message' => Yii::t('app','Введите номер в формате +996 XXX XX XX XX')],
            [
                [
                    'guest_amount',
                    'status',
                    'link_id',
                    'images',
                    'user_id',
                    'reception',
                    'features',
                    'currency',
                    'site',
                    'lat',
                    'lon',

                    'general_room_count',
                    'img'
                ],
                'safe'
            ],
            [['link_id'], 'integer'],
            [['email'], 'email'], // Validate email format
            [['phone'], 'match', 'pattern' => '/^\+?[0-9 ]{7,15}$/'], // Phone validation
            [['lat', 'lon'], 'number'], // Latitude and longitude should be numeric
            //[['description', 'description_en', 'description_ky'], 'string', 'max' => 1000], // Limit description length
        ];
    }


    public static function tableName()
    {
        return 'object';
    }
    public function getCeoDocs()
    {
        $file_array = [];
        $dir = Yii::getAlias("@webroot/uploads/documents/{$this->id}/ceo");
        if (is_dir($dir)) {
            $files = FileHelper::findFiles($dir);
            $counter = 0;
            foreach ($files as $file) {
                $file_array[$counter]['name'] = basename($file);
                $file_array[$counter]['link'] = Url::base() . "/uploads/documents/{$this->id}/ceo/" . basename($file);
                $counter++;
            }
            return $file_array;
        }
        return false;
    }

    public static function cityList()
    {
        $client = Yii::$app->meili->connect()->index('region');
        $cities = $client->search('', [
            'limit' => 100
        ])->getHits();
        return ArrayHelper::map($cities, 'id', 'name');
    }

    public function getFinancialDocs()
    {
        $file_array = [];
        $dir = Yii::getAlias("@webroot/uploads/documents/{$this->id}/financial");
        if (is_dir($dir)) {
            $files = FileHelper::findFiles($dir);
            $counter = 0;
            foreach ($files as $file) {
                $file_array[$counter]['name'] = basename($file);
                $file_array[$counter]['link'] = Url::base() . "/uploads/documents/{$this->id}/financial/" . basename($file);
                $counter++;
            }
            return $file_array;
        }
        return false;
    }

    public static function currentStatus($id, $status = 0)
    {
        $condition_room_tariff = false;
        $model = Objects::findOne($id);
        $tariff_exist = Tariff::find()->where(['object_id' => $id])->one();
        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $object = $index->getDocument($id);
        if (isset($object['rooms']) && is_array($object['rooms']) && $tariff_exist) {
            $condition_room_tariff = true;
            // foreach ($object['rooms'] as $index => $roomData) {
            //     if (isset($roomData['tariff']) && is_array($roomData['tariff'])) {
            //         $condition_room_tariff = true;
            //         break;
            //     }
            // }
        }
        if ($condition_room_tariff && $model->getCeoDocs() && $model->getFinancialDocs()) {
            return self::STATUS_READY_FOR_PUBLISH;
        }
        return $status;
    }

    public static function statusCondition($id, $status = 0)
    {
        $room = 0;
        $tariff = 0;
        $docs = 0;
        $condition_room_tariff = false;


        $client = Yii::$app->meili->connect();
        $index = $client->index('object');
        $model = new Objects();

        try {
            $object = $index->getDocument($id);

            // Check for uploaded docs
            if ($model->getCeoDocs() && $model->getFinancialDocs()) {
                $docs = 1;
            }

            // Check rooms and tariffs
            if (isset($object['rooms']) && is_array($object['rooms']) && !empty($object['rooms'])) {
                $room = 1;

                foreach ($object['rooms'] as $roomData) {
                    if (isset($roomData['tariff']) && is_array($roomData['tariff']) && !empty($roomData['tariff'])) {
                        $tariff = 1;
                        break;
                    }
                }
            }

        } catch (\Exception $e) {
            // Meilisearch document not found or error
            // Optionally log: Yii::error($e->getMessage(), 'meili');
        }

        // Now determine publish condition
        if ($room && $tariff && $docs) {
            return self::STATUS_READY_FOR_PUBLISH;
        }

        return [
            'room' => $room,
            'tariff' => $tariff,
            'docs' => $docs
        ];
    }


    public static function statusData($status = self::STATUS_NOT_PUBLISHED)
    {
        if (!$status || $status == "") {
            $status = self::STATUS_NOT_PUBLISHED;
        }
        $arr = [
            self::STATUS_NOT_PUBLISHED => [
                'label' => Yii::t('app', 'Не опубликовано'),
                'description' => Yii::t('app', 'Заполните информацию об объекте, номерах, тарифах и ценах, чтобы опубликовать'),
                'color' => '#232323',
                'html' => '<div>
                <p class="info-text">Перед публикацией убедитесь что вся необходимая информация внесена:</p>
                    <ul>
                        <li>описание объекта</li>
                        <li>номера и тарифы</li>
                        <li>цены и условия бронирования</li>
                    </ul>
                    <p class="info-text">Опубликуйте объект и после модерации ваш объект появится в результатах поиска и станет доступен для бронирования клиентами</p>
                 </div>',
                'button_text' => Yii::t('app', 'Заполнить информацию'),
                'title' => Yii::t('app', 'Публикация объекта'),
                'current_status' => self::STATUS_NOT_PUBLISHED
            ],
            self::STATUS_ON_MODERATION => [
                'label' => Yii::t('app', 'На модерации'),
                'title' => Yii::t('app', 'На модерации'),
                'description' => Yii::t('app', 'Объект отправлен на проверку. Мы рассмотрим его в ближайшее время'),
                'color' => '#FFBB00',
                'html' => '<div>Ваш объект отправлен на проверку. Мы постараемся рассмотреть его как можно быстрее. Вы получите уведомление, как только объект будет одобрен</div>',
                'button_text' => Yii::t('app', 'Понятно'),
                'title' => Yii::t('app', 'Объект на модерации'),
                'current_status' => self::STATUS_ON_MODERATION
            ],
            self::STATUS_READY_FOR_PUBLISH => [
                'label' => Yii::t('app', 'Готов к публикации'),
                'title' => Yii::t('app', 'Объект готов к публикации'),
                'description' => Yii::t('app', 'Опубликуйте объект - он станет доступен для поиска и бронирования  после проверки'),
                'color' => '#3676BC',
                'html' => 'Вы заполнили все необходимые данные. Опубликуйте объект и после проверки он станет доступен для поиска и бронирования',
                'button_text' => Yii::t('app', 'Опубликовать объект'),
                'current_status' => self::STATUS_READY_FOR_PUBLISH
            ],
            self::STATUS_PUBLISHED => [
                'label' => Yii::t('app', 'Опубликовано'),
                'title' => Yii::t('app', 'Поздравляем! Объект опубликован'),
                'description' => Yii::t('app', 'Объект прошёл модерацию и доступен для бронирования'),
                'color' => '#8CC43D',
                'button_text' => Yii::t('app', 'Снять с публикации'),
                'current_status' => self::STATUS_NOT_PUBLISHED,
                'html' => '<div> Ва ш объект успешно прошёл модерацию и теперь доступен для бронирования!
                        <p class="dialog-paragraph"> 📌 Советы, как получить больше бронирований:</p>
                        <li> - Добавьте яркие и качественные фото</li>
                        <li> - Проверьте цены — они должны быть конкурентными</li>
                        <li> - Убедитесь, что условия бронирования понятны</li></div>'
            ],
            self::STATUS_DENIED => [
                'label' => Yii::t('app', 'Отклонено'),
                'description' => Yii::t('app', 'Объект не прошёл модерацию. Необходимо внести правки и отправить повторно'),
                'color' => '#F5222D',
                'button_text' => Yii::t('app', 'Внести правки'),
                'title' => Yii::t('app', 'Объект отклонён'),
                'current_status' => self::STATUS_DENIED,
                'html' => "<div> К  сожалению, объект не прошёл модерацию. Проверьте данные и внесите необходимые правки. После этого вы сможете отправить объект на повторную проверку.</div>",
            ],
            self::STATUS_DELETED => [
                'label' => Yii::t('app', 'Снят с публикации'),
                'description' => Yii::t('app', 'Объект был снят с публикации'),
                'color' => '#F5222D',
                'button_text' => Yii::t('app', 'Внести правки'),
                'title' => Yii::t('app', 'Объект отклонён'),
                'current_status' => self::STATUS_DELETED,
                'html' => "<div> К  сожалению, объект не прошёл модерацию. Проверьте данные и внесите необходимые правки. После этого вы сможете отправить объект на повторную проверку.</div>",
            ]
        ];
        return $arr[$status];
    }


    public static function statusList()
    {
        $arr = [
            self::STATUS_NOT_PUBLISHED => Yii::t('app', 'Не опубликовано'),
            self::STATUS_ON_MODERATION => Yii::t('app', 'На модерации'),
            self::STATUS_READY_FOR_PUBLISH => Yii::t('app', 'Готов к публикации'),
            self::STATUS_PUBLISHED => Yii::t('app', 'Опубликовано'),
            self::STATUS_DENIED => Yii::t('app', 'Отклонено')
        ];
        return $arr;
    }

    public function lastIncrement()
    {
        try {
            $client = Yii::$app->meili->connect();
            $searchResults = $client->index('object')->search('', [
                'sort' => ['id:desc'],
                'limit' => 1
            ]);
            if (!empty($searchResults->getHits())) {
                $lastDocument = $searchResults->getHits()[0];
                return $lastDocument['id'];
            }

            return 0; // Return 0 if no documents found

        } catch (\Exception $e) {
            Yii::error("Meilisearch error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public static function regionList()
    {
        return ArrayHelper::map(Oblast::find()->all(), 'id', 'title');
    }

    public static function objectTypeList()
    {
        return ArrayHelper::map(Vocabulary::find()->where(['model' => Vocabulary::MODEL_TYPE_OBJECT])->all(), 'id', 'title');
    }


    public static function typeString($id = 1)
    {
        $result = Vocabulary::find()->where(['id' => $id])->one();
        return $result ? $result->title : "";
    }

    public static function objectList()
    {
        $lang = Yii::$app->language;
        $item_arr = [];
        $general_arr = [];
        $index = 0;
        switch ($lang) {
            case 'ru':
                $index = 0;
                break;
            case 'en':
                $index = 1;
                break;
            case 'ky':
                $index = 2;
                break;
            default:
                $index = 0;
                break;
        }

        if (!Yii::$app->user->isGuest) {
            $object_id = 0;
            $label = Yii::t('app', 'Объекты');
            if (Yii::$app->request->get('object_id')) {
                $object_id = Yii::$app->request->get('object_id');
            }
            $filter_string = "user_id=" . Yii::$app->user->id;
            $client = Yii::$app->meili->connect();
            $res = $client->index('object')->search('', [
                'filter' => [
                    $filter_string
                ],
                'limit' => 10000
            ])->getHits();

            foreach ($res as $val) {
                if ($val['id'] == $object_id) {
                    $label = $val['name'][$index];
                } else {
                    $item_arr[] = ['label' => $val['name'][$index], 'url' => ['/owner/object/view', 'object_id' => $val['id']]];
                }
            }
            $item_arr[] = ['label' => Yii::t('app', 'Список объектов'), 'url' => ['/owner/object']];

            $general_arr = [
                'label' => $label,
                'items' => $item_arr,
                'visible' => Yii::$app->user->can('owner') || Yii::$app->user->can('admin'),
                'options' => [
                    'class' => 'owner-nav-item-object',
                ],
            ];
        }

        return $general_arr;
    }

    public static function mealList()
    {
        $title = 'title';
        switch (Yii::$app->language) {
            case 'ru':
                $title = 'title';
                break;
            case 'en':
                $title = 'title_en';
                break;
            case 'ky':
                $title = 'title_ky';
                break;
            default:
                $title = 'title';

        }

        return ArrayHelper::map(Vocabulary::find()->where(['model' => Vocabulary::MODEL_TYPE_MEAL])->all(), 'id', $title);
    }

    public static function mealTypeFull($id)
    {
        $voc = Vocabulary::find()->where(['id' => $id, 'model' => Vocabulary::MODEL_TYPE_MEAL])->one();
        $arr = [
            $voc->title,
            $voc->title_en,
            $voc->title_ky
        ];
        return $arr;
    }

    public function objectTypeString()
    {
        $result = Vocabulary::findOne($this->type);
        return $result ? $result->title : "";
    }



    public function getPictures()
    {
        $list = [];
        foreach ($this->getImages() as $image) {
            $filePath = $image->filePath;
            $thumb = $image->getUrl('220x150');
            $picture = $image->getUrl('500x');
            $original = $image->getPathToOrigin();

            $list[] = [
                'id' => $image->id,
                'picture' => $picture,
                'thumbnailPicture' => $thumb,
                'isMain' => $image->isMain,
                'orignal' => Url::base() . '/' . $original
            ];
        }

        return $list;
    }

    public function getPicture()
    {
        $image = $this->getImage();
        $filePath = $image->filePath;
        // Check if the original image was a webp

        return $this->getImage()->getUrl();

    }

    public static function сomfortList()
    {
        $comfortItems = Comfort::find()->orderBy(['category_id' => SORT_ASC])->all();
        $groupedComfort = [];

        foreach ($comfortItems as $item) {
            $groupedComfort[$item->category_id][] = $item;
        }

        return $groupedComfort;
    }

    public static function roomComfortList()
    {
        $comfortItems = RoomComfort::find()->orderBy(['category_id' => SORT_ASC])->all();
        $groupedComfort = [];

        foreach ($comfortItems as $item) {
            $groupedComfort[$item->category_id][] = $item;
        }

        return $groupedComfort;
    }

    public static function paymentList()
    {
        return ArrayHelper::map(PaymentType::find()->all(), 'id', 'id');
    }

    public function getImageById($id)
    {
        $imageQuery = self::find();
        $finder = $this->getImagesFinder(['id' => $id]);
        $imageQuery->where($finder);

        $img = $imageQuery->one();
        if (!$img) {
            return $this->getModule()->getPlaceHolder();
        }

        return $img;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->img) {
            $image_id = $this->img;
            foreach ($this->getImages() as $image) {
                if ($image->id == $image_id) {
                    $this->setMainImage($image);
                }
            }
        }
    }

    public function imagesPreview($size = [], $thumb = true)
    {
        $images = [];
        foreach ($this->getImages() as $image) {
            $images[] = $thumb ? $image->getUrl($size) : $image->getPathToOrigin();
        }
        return $images;
    }



    public function isImageSet()
    {
        $image = false;
        if ($this->id)
            $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId={$this->id} AND modelName='Objects'")->queryScalar();
        if ($image) {
            return true;
        }
        return $image;
    }

    /**
     * Returns an array of image configuration options for the current model instance.
     * 
     * @return array
     */
    public function imagesPreviewConfig()
    {
        $configs = [];
        foreach ($this->getImages() as $image) {
            $configs[] = [
                'caption' => $image->name,
                'url' => ['/object/remove-image', 'id' => $this->id, 'image_id' => $image->id],
                'key' => $image->id,
            ];
        }
        return $configs;
    }
}
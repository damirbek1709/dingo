<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use rico\yii2images\models\Image;

class Objects extends \yii\db\ActiveRecord {
    public $id;
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


    

    const SEARCH_TYPE_CITY = 3;
    const SEARCH_TYPE_REGION = 1;
    const SEARCH_TYPE_HOTEL = 2;

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

    

    public function rules()
    {
        return [
            [
                [
                    'id',
                    'type',
                    'city',
                    'city_en',
                    'city_ky',
                    'address',
                    'address_en',
                    'address_ky',
                    'currency',
                    'features',
                    'phone',
                    'site',
                    'check_in',
                    'check_out',
                    'reception',
                    'description',
                    'lat',
                    'lon',
                    'email',
                    'name',
                    'uploadImages',
                    'user_id',
                    'images',
                    'img',
                    'name_en',
                    'name_ky',
                    'user_id',
                    'link_id'
                ],
                'safe'
            ],
            [['link_id'], 'integer'],
            [['email'], 'email'], // Validate email format
            [['phone'], 'match', 'pattern' => '/^\+?[0-9 ]{7,15}$/'], // Phone validation
            [['lat', 'lon'], 'number'], // Latitude and longitude should be numeric
            [['description','description_en','description_ky'], 'string', 'max' => 1000], // Limit description length
        ];
    }

    public static function tableName()
    {
        return 'object';
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
        return [
            self::OBJECT_TYPE_APARTHOTEL => 'Апарт-отель',
            self::OBJECT_TYPE_APARTMENTS => 'Апартаменты',
            self::OBJECT_TYPE_RESTBASE => 'База отдыха',
            self::OBJECT_TYPE_BUNGALOW => 'Бунгало',
            self::OBJECT_TYPE_BOUTIQUE_HOTEL => 'Бутик-отель',
            self::OBJECT_TYPE_VILLA => 'Вилла',
            self::OBJECT_TYPE_GLAMPING => 'Глэмпинг',
            self::OBJECT_TYPE_GUESTHOUSE => 'Гостевой дом',
            self::OBJECT_TYPE_RESIDENTIAL_PREMISES => 'Жилое помещение',
        ];

    }

    public function objectTypeString()
    {
        $arr = [
            self::OBJECT_TYPE_APARTHOTEL => 'Апарт-отель',
            self::OBJECT_TYPE_APARTMENTS => 'Апартаменты',
            self::OBJECT_TYPE_RESTBASE => 'База отдыха',
            self::OBJECT_TYPE_BUNGALOW => 'Бунгало',
            self::OBJECT_TYPE_BOUTIQUE_HOTEL => 'Бутик-отель',
            self::OBJECT_TYPE_VILLA => 'Вилла',
            self::OBJECT_TYPE_GLAMPING => 'Глэмпинг',
            self::OBJECT_TYPE_GUESTHOUSE => 'Гостевой дом',
            self::OBJECT_TYPE_RESIDENTIAL_PREMISES => 'Жилое помещение',
        ];
        return $arr[$this->type];
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => Yii::t('app', 'Тип'),
            'city' => Yii::t('app', 'Город'),
            'address' => Yii::t('app', 'Адрес'),
            'currency' => Yii::t('app', 'Валюта'),
            'features' => Yii::t('app', 'Адрес'),
            'phone' => Yii::t('app', 'Телефон'),
            'site' => Yii::t('app', 'Сайт'),
            'check_in' => Yii::t('app', 'Заезд'),
            'check_out' => Yii::t('app', 'Выезд'),
            'reception' => Yii::t('app', 'Ресепшн'),
            'description' => Yii::t('app', 'Описание'),
            'lat' => 'Latitude',
            'lon' => 'Longitude',
            'email' => 'E-mail',
            'name' => Yii::t('app', 'Название'),
            'name_en' => Yii::t('app', 'Название на английском'),
            'name_ky' => Yii::t('app', 'Название на кыргызском'),

            'city_en' => Yii::t('app', 'Город на английском'),
            'city_ky' => Yii::t('app', 'Город на кыргызском'),

            'address_en' => Yii::t('app', 'Адрес на английском'),
            'address_ky' => Yii::t('app', 'Адрес на кыргызском'),

            'description_en' => Yii::t('app', 'Описание на английском'),
            'description_ky' => Yii::t('app', 'Описание на кыргызском'),
        ];
    }

    public static function mealList()
    {
        return [
            (int) self::TERM_MEAL_BREAKFEST => Yii::t('app', 'Завтрак'),
            (int) self::TERM_MEAL_THREE_TIMES => Yii::t('app', 'Трехразовое питание'),
        ];
    }

    public function getPictures()
    {
        $list = [];
        foreach ($this->getImages() as $image) {
            $filePath = $image->filePath;
            $thumb = $image->getUrl('220x150');
            $picture = $image->getUrl('500x');
            // Check if the original image was a webp
            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp') {
                $img_url = 'https://dingo.kg/uploads/images/store/' . $filePath;
                $picture = 'https://dingo.kg/uploads/images/store/' . $filePath;
            }
            $list[] = [
                'id' => $image->id,
                'picture' => $picture,
                'thumbnailPicture' => $thumb,
                'isMain' => $image->isMain,
            ];
        }

        return $list;
    }

    public function getPicture()
    {
        $image = $this->getImage();
        $filePath = $image->filePath;
        // Check if the original image was a webp
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp') {
            return 'https://dingo.kg/uploads/images/store/' . $filePath;
        } else {
            return $this->getImage()->getUrl();
        }
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

?>
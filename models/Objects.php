<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use rico\yii2images\models\Image;

class Objects extends Model
{
    public $id;
    public $user_id;
    public $type;
    public $name;
    public $name_en;
    public $name_ky;
    public $city;
    public $address;
    public $currency;
    public $features;
    public $phone;
    public $site;
    public $check_in;
    public $check_out;
    public $object;
    public $reception;
    public $description;
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
    public $primaryKey = 'id';
    public $children = [];

    const COMFORT_CATEGORY_SERVICE = 1;
    const COMFORT_CATEGORY_SPORT = 2;
    const COMFORT_CATEGORY_GENERAL = 3;
    const COMFORT_CATEGORY_POOL = 4;
    const COMFORT_CATEGORY_CHILDREN = 5;
    const COMFORT_CATEGORY_WORK = 6;

    const SEARCH_TYPE_CITY = 3;
    const SEARCH_TYPE_REGION = 1;
    const SEARCH_TYPE_HOTEL = 2;

    public $comfort_list;

    const TERM_MEAL_BREAKFEST = 1;
    const TERM_MEAL_THREE_TIMES = 2;

    public function rules()
    {
        return [
            [
                [
                    'id',
                    'type',
                    'city',
                    'address',
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
                    'name_ky'
                ],
                'safe'
            ],
            [['email'], 'email'], // Validate email format
            [['phone'], 'match', 'pattern' => '/^\+?[0-9 ]{7,15}$/'], // Phone validation
            [['lat', 'lon'], 'number'], // Latitude and longitude should be numeric
            [['description'], 'string', 'max' => 1000], // Limit description length
        ];
    }

    public static function regionList()
    {
        return ArrayHelper::map(Oblast::find()->all(), 'id', 'title');
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
            'type' => 'Type',
            'city' => 'City',
            'address' => 'Address',
            'currency' => 'Currency',
            'features' => 'Features',
            'phone' => 'Phone',
            'site' => 'Website',
            'check_in' => 'Check-in Time',
            'check_out' => 'Check-out Time',
            'reception' => 'Reception Hours',
            'description' => 'Description',
            'lat' => 'Latitude',
            'lon' => 'Longitude',
            'email' => 'Email',
        ];
    }

    public static function mealList()
    {
        return [
            (int) self::TERM_MEAL_BREAKFEST => Yii::t('app', 'Завтрак'),
            (int) self::TERM_MEAL_THREE_TIMES => Yii::t('app', 'Трехразовое питание'),
        ];
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
        $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId={$this->id} AND modelName='Objects'")->queryScalar();
        if ($image) {
            return true;
        }
        return false;
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
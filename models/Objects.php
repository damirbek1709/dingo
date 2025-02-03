<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use rico\yii2images\models\Image;

class Objects extends Model
{
    public $id;
    public $primaryKey = ['id'];
    public $user_id;
    public $type;
    public $name;
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
    public $lat;
    public $lon;
    public $email;
    public $uploadImages;
    public $images;
    public $img;

    public function rules()
    {
        return [
            [['id', 'type', 'city', 'address', 'currency', 'features', 'phone', 'site', 
              'check_in', 'check_out', 'reception', 'description', 'lat', 'lon', 'email','name','uploadImages','user_id','images','img'], 'safe'],
            [['email'], 'email'], // Validate email format
            [['phone'], 'match', 'pattern' => '/^\+?[0-9 ]{7,15}$/'], // Phone validation
            [['lat', 'lon'], 'number'], // Latitude and longitude should be numeric
            [['description'], 'string', 'max' => 1000], // Limit description length
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
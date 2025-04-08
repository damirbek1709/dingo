<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "room_cat".
 *
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $title_ky
 * @property int $guest_amount
 * @property int $similar_room_amount
 * @property float $area
 * @property int|null $bathroom
 * @property int|null $balcony
 * @property int|null $air_cond
 * @property int|null $kitchen
 * @property float|null $base_price
 * @property float|null $img
 */
class RoomCat extends \yii\db\ActiveRecord
{
    public $images;
    public $room_title;
    public $tariff;
    public $comfort;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_cat';
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    public function getPictures()
    {
        $list = [];
        foreach ($this->getImages() as $image) {
            $filePath = $image->filePath;
            $img_url = $image->getUrl('120x');
            $picture = $image->getUrl('500x');
            // Check if the original image was a webp
            // if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp') {
            //     $img_url = 'https://selva.kg/uploads/images/store/' . $filePath;
            //     $picture = 'https://selva.kg/uploads/images/store/' . $filePath;
            // }
            $list[] = [
                'id' => $image->id,
                'picture' => $picture,
                'thumbnailPicture' => $img_url,
                'isMain' => $image->isMain,
            ];
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['guest_amount', 'similar_room_amount', 'area'], 'required'],
            [['guest_amount', 'similar_room_amount', 'bathroom', 'balcony', 'air_cond', 'kitchen','type_id'], 'integer'],
            [['area', 'base_price','title'], 'number'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'title_en' => Yii::t('app', 'Title En'),
            'title_ky' => Yii::t('app', 'Title Ky'),
            'guest_amount' => Yii::t('app', 'Количество гостей'),
            'similar_room_amount' => Yii::t('app', 'Количество подобных комнат'),
            'area' => Yii::t('app', 'Площадь'),
            'bathroom' => Yii::t('app', 'Ванная'),
            'balcony' => Yii::t('app', 'Балкон'),
            'air_cond' => Yii::t('app', 'Кондиционер'),
            'kitchen' => Yii::t('app', 'Кухня'),
            'base_price' => Yii::t('app', 'Базовая цена'),
            'img' => Yii::t('app', 'Фото'),
        ];
    }

    public function typeList()
    {
        return ArrayHelper::map(RoomType::find()->all(), 'id', 'title');
    }

    public function typeTitle($id)
    {
        return RoomType::findOne($id)->title ? RoomType::findOne($id)->title : '';
    }

    public function getWallpaper()
    {
        if (is_dir(Yii::getAlias("@webroot/images/product/cover/{$this->id}"))) {
            $array = FileHelper::findFiles(Yii::getAlias("@webroot/images/roomcat/cover/{$this->id}"), [
                'recursive' => false,
                'except' => ['.gitignore']
            ]);
            if (!empty($array)) {
                return Url::base() . "/images/roomcat/cover/{$this->id}/" . basename($array[0]);
            }
        } else {
            return Url::base() . "/images/site/template.png";
        }
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
        $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId=$this->id AND modelName='RoomCat'")->queryScalar();
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
                'url' => ['/room-cat/remove-image', 'id' => $this->id, 'image_id' => $image->id],
                'key' => $image->id,
            ];
        }
        return $configs;
    }
}

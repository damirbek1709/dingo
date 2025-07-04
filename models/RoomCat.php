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
    public $is_paid;
    public $not_available_dates;
    public $default_prices = [];
    public $room_left;
    public $main_img;



    const BED_TYPE_ONE = 1;
    const BED_TYPE_TWO = 2;
    const BED_TYPE_THREE = 3;
    const BED_TYPE_FOUR = 4;
    const BED_TYPE_FIVE = 5;
    const BED_TYPE_SIX = 6;

    public $bed_types;
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

    public function bedTypes()
    {

        return [
            self::BED_TYPE_ONE => [
                ['Односпальная кровать', '2 гостя, ширина 90-130см'],
                [
                    'Single bed',
                    '2 guests, width 90-130cm',
                ],
                [
                    'Бир адамдык керебет',
                    '2 конок, туурасы 90-130см',
                ]
            ],
            self::BED_TYPE_TWO => [
                ['Двуспальная Queen size', '2 гостя, ширина 151-180см'],
                [
                    'Queen size double bed',
                    '2 guests, width 151-180cm',
                ],
                [
                    'Queen size эки адамдык керебет',
                    '2 конок, туурасы 151-180см',
                ]
            ],
            self::BED_TYPE_THREE => [
                ['Двуспальная King size', '2 гостя, ширина 181-210см'],
                [
                    'King size double bed',
                    '2 guests, width 181-210cm',
                ],
                [
                    'King size эки адамдык керебет',
                    '2 конок, туурасы 181-210см',
                ]
            ],
            self::BED_TYPE_FOUR => [
                ['Двухъярусная кровать', '2 гостя, ширина 90-130см'],
                [
                    'Bunk bed',
                    '2 guests, width 90-130cm',
                ],
                [
                    'Эки кабаттуу керебет',
                    '2 конок, туурасы 90-130см',
                ]
            ],
            self::BED_TYPE_FIVE => [
                ['Диван-кровать', '2 гостя, ширина 131-150см'],
                [
                    'Sofa bed',
                    '2 guests, width 131-150cm',
                ],
                [
                    'Диван-керебет',
                    '2 конок, туурасы 131-150см',
                ]
            ],
            self::BED_TYPE_SIX => [
                ['Кресло-кровать', '1 гость, ширина 60-130см'],
                [
                    'Armchair bed',
                    '1 guest, width 60-130cm',
                ],
                [
                    'Кресло-керебет',
                    '1 конок, туурасы 60-130см',
                ]
            ],
        ];
    }


    public function getPictures()
    {
        $list = [];
        foreach ($this->getImages() as $image) {
            $filePath = $image->filePath;
            $img_url = $image->getUrl('120x');
            $picture = $image->getUrl('500x');
            $original = $image->getPathToOrigin();
            // Check if the original image was a webp
            // if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'webp') {
            //     $img_url = 'https://selva.kg/uploads/images/store/' . $filePath;
            //     $picture = 'https://selva.kg/uploads/images/store/' . $filePath;
            // }
            $list[] = [
                'id' => $image->id,
                'picture' => $picture,
                'thumbnailPicture' => $img_url,
                'orignal' => Url::base() . '/' . $original,
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
            [['similar_room_amount', 'area'], 'required'],
            [['bed_types', 'guest_amount', 'default_prices', 'room_left','main_img'], 'safe'],
            [['guest_amount'], 'default', 'value' => 1],
            [['guest_amount', 'similar_room_amount', 'bathroom', 'balcony', 'air_cond', 'kitchen', 'type_id'], 'integer'],
            [['area', 'base_price', 'title'], 'number'],
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
            'images' => Yii::t('app', 'Фотографии'),
            'default_prices' => Yii::t('app', 'Базовые цены'),
            'room_left' => Yii::t('app', 'Доступно номеров'),
            'main_img' => Yii::t('app', 'Главное изображение'),
        ];
    }

    public function typeList()
    {
        return ArrayHelper::map(Vocabulary::find()->where(['model' => Vocabulary::MODEL_TYPE_ROOM])->all(), 'id', 'title');
    }



    public function typeTitle($id)
    {
        $result = Vocabulary::find()->where(['model' => Vocabulary::MODEL_TYPE_ROOM, 'id' => $id])->one();
        return $result ? [$result->title, $result->title_en, $result->title_ky, $id] : [];
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
        $img = Image::find()->where(['id'=>$id])->one();
        if (!$img) {
            return $this->getModule()->getPlaceHolder();
        }

        return $img;
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

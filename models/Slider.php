<?php

namespace app\models;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use rico\yii2images\behaviors\ImageBehave;

/**
 * This is the model class for table "slider".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $title_ky
 * @property string|null $title_en
 * @property string|null $image
 */
class Slider extends \yii\db\ActiveRecord
{
    public $file;
    public $image;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'slider';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title_en'], 'required'],
            [['link'], 'safe'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp,gif', 'maxFiles' => 1],
            //['file', ImageValidator::class, 'minWidth' => 1920, 'minHeight' => 800],
        ];
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehave::className(),
            ],
        ];
    }



    public function imagesPreview($size = [], $thumb = true)
    {
        $images = [];
        foreach ($this->getImages() as $image) {
            $images[] = $thumb ? $image->getUrl($size) : $image->getPathToOrigin();
        }
        return $images;
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
                'url' => ['/slider/remove-image', 'id' => $this->id, 'image_id' => $image->id],
                'key' => $image->id,
            ];
        }

        return $configs;
    }

    public function getWallpaper()
    {
        return $this->getImage()->getUrl('200x230');
    }

    public function getPicture($size)
    {
        return $this->getImage()->getUrl($size);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'title_en' => 'Заголовок на английском',
            'image'=>'Изображение',
            'link'=>'Ссылка',
        ];
    }

    public function isImageSet()
    {
        $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId={$this->id} AND modelName='slider'")->queryScalar();
        if ($image) {
            return true;
        }
        return false;
    }

    function getThumbImages()
    {
        $result = [
            Html::img($this->getPicture('150x120'))
        ];
        return $result;
    }

    function getThumbs()
    {
        $result = [
            Html::img($this->getPicture('150x120'))
        ];
        return $result;
    }

    function translate($language)
    {
        switch ($language) {
            case "en":
                if ($this->title_en != null) $this->title = $this->{"title_en"};
                else $this->title = $this->{"title"};
                break;
            default:
                $this->title = $this->{"title"};
        }
    }
}

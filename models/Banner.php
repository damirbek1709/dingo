<?php

namespace app\models;

use Yii;
use rico\yii2images\behaviors\ImageBehave;
use yii\helpers\Html;

/**
 * This is the model class for table "banner".
 *
 * @property int $id
 * @property string $title
 * @property string $link
 */
class Banner extends \yii\db\ActiveRecord
{
    public $image;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banner';
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehave::className(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'link'], 'required'],
            [['title', 'link'], 'string', 'max' => 255],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp,gif', 'maxFiles' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Заголовок'),
            'link' => Yii::t('app', 'Ссылка'),
            'image' => Yii::t('app', 'Изображение'),
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
                'url' => ['/banner/remove-image', 'id' => $this->id, 'image_id' => $image->id],
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

    public function isImageSet()
    {
        $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId={$this->id} AND modelName='banner'")->queryScalar();
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
}

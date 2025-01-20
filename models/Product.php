<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;
use app\models\Category;
use rico\yii2images\models\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use rico\yii2images\behaviors\ImageBehave;
use creocoder\taggable\TaggableBehavior;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $title
 * @property float $price
 * @property int $category_id
 * @property float $new_price
 * @property string $delivery
 * @property string $details
 * @property int $in_stock
 * @property int $product_code
 * @property string $description
 * @property string $size
 */
class Product extends \yii\db\ActiveRecord
{
    const CATEGORY_CLOTHING = 1;
    const CATEGORY_ACCESORIES = 2;
    const CATEGORY_DECORATION = 3;

    public $cover;
    public $main_img_id;
    public $images = [];
    public $tagNames;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    public function getPrice()
    {
        return $this->price;
    }
    public static function getCategoryList()
    {
        return ArrayHelper::map(Category::find()->asArray()->all(), 'id', 'name');
    }

    public static function getCategoryListMain()
    {
        return ArrayHelper::map(Category::find()->where(['parent_id' => 0])->all(), 'id', 'name');
    }

    public function getLabel()
    {
        return $this->title;
    }

    public function getUniqueId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'price', 'category_id', 'delivery', 'details', 'product_code', 'description', 'composition'], 'required'],
            [['price', 'new_price', 'main_img_id', 'new_price'], 'number'],
            [['new_price'], 'compare', 'compareAttribute' => 'price', 'operator' => '<', 'type' => 'number', 'message' => Yii::t('app', 'New price must be less than the original price.')],
            [['category_id', 'in_stock', 'product_code','best_seller','new_delivery'], 'integer'],
            [['title', 'size', 'title_en', 'description_en'], 'string', 'max' => 255],
            [['delivery', 'details', 'details_en'], 'string', 'max' => 400],
            [['description'], 'string', 'max' => 500],
            [['cover'], 'file', 'mimeTypes' => 'image/jpeg, image/png', 'maxSize' => 5000000],
            [['images'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp,gif'],
            [['tagNames'], 'safe'], // Validation rule for tags
            [['product_code'], 'unique'], // Validation rule for tags
        ];
    }

    public function getCategory()
    {
        $rel = $this->hasOne(Category::class, ['id' => 'category_id']);
        if ($rel->exists()) {
            return $rel;
        }
        return null;
    }

    public function getWallpaper()
    {
        if (is_dir(Yii::getAlias("@webroot/images/product/cover/{$this->id}"))) {
            $array = FileHelper::findFiles(Yii::getAlias("@webroot/images/product/cover/{$this->id}"), [
                'recursive' => false,
                'except' => ['.gitignore']
            ]);
            if (!empty($array)) {
                return Url::base() . "/images/product/cover/{$this->id}/" . basename($array[0]);
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

    public function beforeValidate()
    {
        $this->cover = UploadedFile::getInstance($this, 'cover');
        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->main_img_id) {
            $image_id = $this->main_img_id;
            foreach ($this->getImages() as $image) {
                if ($image->id == $image_id) {
                    $this->setMainImage($image);
                }
            }
        }
    }

    public function isImageSet()
    {
        $image = Yii::$app->db->createCommand("SELECT * FROM image WHERE itemId={$this->id} AND modelName='Product'")->queryScalar();
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

    public static function getCategoryDropdownTree()
    {
        $categories = Category::find()->all();
        $tree = self::buildCategoryDropdownTree($categories);
        return $tree;
    }


    private static function buildCategoryDropdownTree($categories, $parentId = null, $indent = '')
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = self::buildCategoryDropdownTree($categories, $category->id, $indent . ' - ');
                $label = $indent . $category->name;

                if (!empty($children)) {
                    $label .= ' (' . count($children) . ')';
                }

                $tree[$category->id] = $label;

                if (!empty($children)) {
                    $tree += $children;
                }
            }
        }
        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Заголовок'),
            'title_en' => Yii::t('app', 'Заголовок на английском'),
            'price' => Yii::t('app', 'Цена'),
            'category_id' => Yii::t('app', 'Категория'),
            'new_price' => Yii::t('app', 'Новая цена'),
            'delivery' => Yii::t('app', 'Доставка'),
            'details' => Yii::t('app', 'Детали'),
            'in_stock' => Yii::t('app', 'В наличии'),
            'product_code' => Yii::t('app', 'Код продукта'),
            'description' => Yii::t('app', 'Описание'),
            'description_en' => Yii::t('app', 'Описание на английском'),
            'size' => Yii::t('app', 'Размер'),
            'composition' => Yii::t('app', 'Состав'),
            'images' => Yii::t('app', 'Фотографии'),
            'details_en' => Yii::t('app', 'Детали на английском'),
            'best_seller' => Yii::t('app', 'Бестселлер'),
            'new_delivery' => Yii::t('app', 'Новинка')
        ];
    }

    public function isFav()
    {
        if (isset($_COOKIE['fav'])) {
            $favorites = $_COOKIE['fav'];
            if ($favorites) {
                if (in_array($this->id, unserialize($favorites))) {
                    return "red-heart";
                }
            }
        }
        return "";
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehave::className(),
            ],
        ];
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('product_tag', ['product_id' => 'id']);
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
                'url' => ['/product/remove-image', 'id' => $this->id, 'image_id' => $image->id],
                'key' => $image->id,
            ];
        }
        return $configs;
    }

    function translate($language)
    {
        switch ($language) {
            case "en":
                if ($this->title_en != null)
                    $this->title = $this->{"title_en"};
                else
                    $this->title = $this->{"title"};

                // if ($this->text_en != null) $this->text = $this->{"text_en"};
                // else $this->text = $this->{"text"};

                if ($this->description_en != null)
                    $this->description = $this->{"description_en"};
                else
                    $this->description = $this->{"description"};

                if ($this->details_en != null)
                    $this->details = $this->{"details_en"};
                else
                    $this->details = $this->{"details"};
                break;
            default:
                $this->title = $this->{"title"};
                //$this->text = $this->{"text"};
                $this->description = $this->{"description"};
                $this->details = $this->{"details"};
        }
    }
}

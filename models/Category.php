<?php
namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'parent_id'], 'required'],
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    
    private static function buildCategoryTree($categories, $parentId = null)
    {
        $tree = [];
        foreach ($categories as $category) {
            $category->translate(Yii::$app->language);
            if ($category->parent_id == $parentId) {
                $menuItem = [
                    'label' => $category->name,
                    'url' => ['/product/index', 'id' => $category->id], // Adjust the URL as needed
                    'options' => [
                        'class' => 'main-item main-list-1', // Adjust the class as needed
                    ],
                ];

                $children = self::buildCategoryTree($categories, $category->id);
                if (!empty($children)) {
                    $menuItem['items'] = $children;
                }

                $tree[] = $menuItem;
            }
        }
        return $tree;
    }

    public function setChildren($value)
    {
        $this->children = $value;
    }

    public static function getCategoryTree()
    {
        $categories = self::find()->all();
        $tree = self::buildCategoryTree($categories);
        return $tree;
    }

    public function getParent(){
        return $this->hasOne(self::className(), ['id'=> 'parent_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'parent_id' => Yii::t('app', 'Родительская категория'),
        ];
    }

    function translate($language)
    {
        switch ($language) {
            case "en":
                if ($this->name_en != null) $this->name = $this->{"name_en"};
                else $this->name = $this->{"name"};
                break;
            default:
                $this->name = $this->{"name"};
        }
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }
}

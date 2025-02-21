<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comfort".
 *
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $title_ky
 * @property int|null $category_id
 */
class Comfort extends \yii\db\ActiveRecord
{
    const COMFORT_CAT_SERVICE = 1;
    const COMFORT_CAT_SPORT = 2;
    const COMFORT_CAT_GENERAL = 3;
    const COMFORT_CAT_POOL = 4;
    const COMFORT_CAT_CHILDREN = 5;
    const COMFORT_CAT_WORK = 6;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comfort';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['category_id'], 'integer'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
        ];
    }

    public static function getComfortCategoryTitle($id)
    {
        $arr = [
            self::COMFORT_CAT_SERVICE => Yii::t('app', 'Услуги'),
            self::COMFORT_CAT_SPORT => Yii::t('app', 'Спорт и отдых'),
            self::COMFORT_CAT_GENERAL => Yii::t('app', 'Общее'),
            self::COMFORT_CAT_POOL => Yii::t('app', 'Бассейн и пляж'),
            self::COMFORT_CAT_CHILDREN => Yii::t('app', 'Для детей'),
            self::COMFORT_CAT_WORK => Yii::t('app', 'Для работы'),
        ];
        return $arr[$id];
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
            'category_id' => Yii::t('app', 'Category ID'),
        ];
    }
}

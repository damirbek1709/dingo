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
    const COMFORT_CAT_AVAILABILITY = 7;
    const COMFORT_CAT_ANIMALS = 8;
    const COMFORT_CAT_WINTER_SPORTS = 9;
    const COMFORT_CAT_INTERNET = 10;
    const COMFORT_CAT_BEATY = 11;
    const COMFORT_CAT_PARKING = 12;
    const COMFORT_CAT_STAFF_SPEAKS = 13;
    const COMFORT_CAT_TRANSFER = 14;
    const COMFORT_CAT_SANITAR = 15;
    const COMFORT_CAT_IN_ROOMS = 16;

    public $is_paid;
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
            self::COMFORT_CAT_SERVICE => Yii::t('app', 'Услуги и особенности'),
            self::COMFORT_CAT_SPORT => Yii::t('app', 'Спорт и отдых'),
            self::COMFORT_CAT_GENERAL => Yii::t('app', 'Общее'),
            self::COMFORT_CAT_POOL => Yii::t('app', 'Бассейн и пляж'),
            self::COMFORT_CAT_CHILDREN => Yii::t('app', 'Для детей'),
            self::COMFORT_CAT_WORK => Yii::t('app', 'Для работы'),

            self::COMFORT_CAT_AVAILABILITY => Yii::t('app', 'Доступность'),
            self::COMFORT_CAT_ANIMALS => Yii::t('app', 'Животные'),
            self::COMFORT_CAT_WINTER_SPORTS => Yii::t('app', 'Зимние виды спорта'),
            self::COMFORT_CAT_INTERNET => Yii::t('app', 'Интернет'),
            self::COMFORT_CAT_BEATY => Yii::t('app', 'Красота и здоровье'),
            self::COMFORT_CAT_PARKING => Yii::t('app', 'Парковка'),
            self::COMFORT_CAT_STAFF_SPEAKS => Yii::t('app', 'Персонал говорит'),
            self::COMFORT_CAT_TRANSFER => Yii::t('app', 'Трансфер'),
            self::COMFORT_CAT_SANITAR => Yii::t('app', 'Санитарные меры'),
            self::COMFORT_CAT_IN_ROOMS => Yii::t('app', 'В номерах'),
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

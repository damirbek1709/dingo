<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "room_comfort".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $title_en
 * @property string|null $title_ky
 */
class RoomComfort extends \yii\db\ActiveRecord
{
    const ROOM_COMFORT_SPORT = 1;
    const ROOM_COMFORT_GENERAL = 2;
    const ROOM_COMFORT_TV = 12;
    const ROOM_COMFORT_BATHROOM = 3;
    const ROOM_COMFORT_INSIDE = 4;
    const ROOM_COMFORT_EXTRA = 5;
    const ROOM_COMFORT_MEAL = 6;
    const ROOM_COMFORT_OUTSIDE = 7;
    const ROOM_COMFORT_LAUNDRY = 8;
    const ROOM_COMFORT_POOL = 9;
    const ROOM_COMFORT_INTERNET = 10;
    const ROOM_COMFORT_BEAUTY = 11;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_comfort';
    }

    public static function getComfortCategoryTitle($id)
    {
        $arr = [
            self::ROOM_COMFORT_SPORT => Yii::t('app', 'Спорт и отдых'),
            self::ROOM_COMFORT_GENERAL => Yii::t('app', 'Общее'),
            self::ROOM_COMFORT_BATHROOM => Yii::t('app', 'Ванная'),
            self::ROOM_COMFORT_INSIDE => Yii::t('app', 'В номерах'),
            self::ROOM_COMFORT_EXTRA => Yii::t('app', 'Дополнительно'),
            self::ROOM_COMFORT_MEAL => Yii::t('app', 'Питание'),
            self::ROOM_COMFORT_OUTSIDE => Yii::t('app', 'Вне помещения и вид'),
            self::ROOM_COMFORT_LAUNDRY => Yii::t('app', 'Стирка'),
            self::ROOM_COMFORT_POOL => Yii::t('app', 'Бассейн и пляж'),
            self::ROOM_COMFORT_INTERNET => Yii::t('app', 'Интернет'),
            self::ROOM_COMFORT_BEAUTY => Yii::t('app', 'Красота и здоровье'),
            self::ROOM_COMFORT_TV => Yii::t('app', 'Телевидение и техника'),
            
        ];
        return $arr[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
            [['category_id'], 'integer', 'max' => 5],
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
        ];
    }
}

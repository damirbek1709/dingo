<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "room_type".
 *
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $title_ky
 */
class RoomType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'room_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title','room_amount'], 'required'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
        ];
    }

    public static function сomfortList()
    {
        $comfortItems = RoomComfort::find()->orderBy(['category_id' => SORT_ASC])->all();
        $groupedComfort = [];

        foreach ($comfortItems as $item) {
            $groupedComfort[$item->category_id][] = $item;
        }

        return $groupedComfort;
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
            'room_amount' => Yii::t('app', 'Количество гостей'),
        ];
    }
}

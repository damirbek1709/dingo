<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notification_list".
 *
 * @property int $id
 * @property int $category
 * @property string $title
 * @property string $text
 * @property string $title_en
 * @property string $text_en
 * @property string $title_ky
 * @property string $text_ky
 */
class NotificationList extends \yii\db\ActiveRecord
{
    const CATEGORY_OBJECT = 1;
    const CATEGORY_BOOKING = 2;
    /**
     * {@inheritdoc}
     */

     public function getCategoryList()  {
        return [
            self::CATEGORY_OBJECT =>'Объекты',
            self::CATEGORY_BOOKING=>'Брони'
        ];
     }
    public static function tableName()
    {
        return 'notification_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category', 'title', 'text', 'title_en', 'text_en', 'title_ky', 'text_ky'], 'required'],
            [['category'], 'integer'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
            [['text', 'text_en', 'text_ky'], 'string', 'max' => 500],
        ];
    }



    

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'Category',
            'title' => 'Title',
            'text' => 'Text',
            'title_en' => 'Title En',
            'text_en' => 'Text En',
            'title_ky' => 'Title Ky',
            'text_ky' => 'Text Ky',
        ];
    }
}

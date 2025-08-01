<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int $type
 * @property string $title
 * @property string $text
 * @property int $status
 * @property string $date
 */
class Notification extends \yii\db\ActiveRecord
{
    const TYPE_OBJECT = 1;
    const TYPE_BOOKING = 2;

    const STATUS_READ = 1;
    const STATUS_NOT_READ = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'title', 'text','user_id','category','model_id'], 'required'],
            [['type', 'status','user_id','category','model_id'], 'integer'],
            [['date'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'title' => 'Title',
            'text' => 'Text',
            'status' => 'Status',
            'date' => 'Date',
        ];
    }

    public function fields()
    {
        return [
            'id','type','model_id','titleList','textList','status','date'
        ];
    }

    public function getTitleList()  {
        return [
            $this->title,
            $this->title_en,
            $this->title_ky
        ];
    }

    public function getTextList()  {
        return [
            $this->text,
            $this->text_en,
            $this->text_ky
        ];
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "page".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $title_en
 * @property string $text_en
 * @property string $title_ky
 * @property string $text_ky
 */
class Page extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'text'], 'required'],
            [['title_en', 'text_en', 'title_ky', 'text_ky'], 'safe'],
            [['text', 'text_en', 'text_ky'], 'string'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'title_en' => 'Заголовок на английском',
            'text_en' => 'Текст на английском',
            'title_ky' => 'Заголовок на кыргызском',
            'text_ky' => 'Текст на кыргызском',
        ];
    }
}

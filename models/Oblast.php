<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oblast".
 *
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $title_ky
 */
class Oblast extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'oblast';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
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

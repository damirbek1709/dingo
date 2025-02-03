<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "features".
 *
 * @property int $id
 * @property string $title
 * @property string $title_ky
 * @property string $title_en
 * @property string|null $img
 */
class Features extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'features';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'title_ky', 'title_en'], 'required'],
            [['title', 'title_ky', 'title_en', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Название'),
            'title_ky' => Yii::t('app', 'Название на кыргызском'),
            'title_en' => Yii::t('app', 'Название на английском'),
            'img' => Yii::t('app', 'Img'),
        ];
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vocabulary".
 *
 * @property int $id
 * @property string $title
 * @property string $title_en
 * @property string $title_ky
 * @property int|null $model
 */
class Vocabulary extends \yii\db\ActiveRecord
{
    const MODEL_TYPE_MEAL = 1;
    const MODEL_TYPE_OBJECT = 2;
    const MODEL_TYPE_ROOM = 3;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vocabulary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'title_en', 'title_ky'], 'required'],
            [['model'], 'integer'],
            [['title', 'title_en', 'title_ky'], 'string', 'max' => 255],
        ];
    }

    public static function list()
    {
        $arr = [
            self::MODEL_TYPE_MEAL => Yii::t('app', 'Тип Питания'),
            self::MODEL_TYPE_OBJECT => Yii::t('app', 'Тип объекта'),
            self::MODEL_TYPE_ROOM => Yii::t('app', 'Тип номера'),
        ];
        return $arr;
    }

    // public static function listModel($id)
    // {
    //     $arr = [
    //         self::MODEL_TYPE_MEAL => Yii::t('app', 'Тип Питания'),
    //         self::MODEL_TYPE_OBJECT => Yii::t('app', 'Тип объекта'),
    //         self::MODEL_TYPE_ROOM => Yii::t('app', 'Тип номера'),
    //     ];
    //     return $arr[$id];
    // }

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
            'model' => Yii::t('app', 'Model'),
        ];
    }
}

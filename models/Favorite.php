<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "favorite".
 *
 * @property int $id
 * @property int $object_id
 * @property int $user_id
 */
class Favorite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'favorite';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_id', 'user_id'], 'required'],
            [['object_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }
}

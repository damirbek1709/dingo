<?php

namespace app\models;

use app\models\user\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "feedback".
 *
 * @property int $id
 * @property int $object_id
 * @property int $user_id
 * @property int|null $general
 * @property int|null $cleaning
 * @property int|null $location
 * @property int|null $room
 * @property int|null $meal
 * @property int|null $hygien
 * @property int|null $price_quality
 * @property int|null $service
 * @property int|null $wifi
 * @property string|null $pos
 * @property string $cons
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_id', 'user_id'], 'required'],
            [['object_id'], 'integer'],
            [['general', 'cleaning', 'location', 'room', 'meal', 'hygien', 'price_quality', 'service', 'wifi'], 'integer', 'max' => 9],
            [['pos', 'cons'], 'string', 'max' => 800],
            [['created_at'], 'safe'],
            [['created_at'], 'default', 'value' => date('Y-m-d H:i:s')],
        ];
    }

    public function fields()
    {
        return ['pos', 'cons', 'name'];
    }

    public function getName()
    {
        $user = User::findOne($this->user_id);
        return $user->name ?? $user->email;
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'general' => Yii::t('app', 'General'),
            'cleaning' => Yii::t('app', 'Cleaning'),
            'location' => Yii::t('app', 'Location'),
            'room' => Yii::t('app', 'Room'),
            'meal' => Yii::t('app', 'Meal'),
            'hygien' => Yii::t('app', 'Hygien'),
            'price_quality' => Yii::t('app', 'Price Quality'),
            'service' => Yii::t('app', 'Service'),
            'wifi' => Yii::t('app', 'Wifi'),
            'pos' => Yii::t('app', 'Pos'),
            'cons' => Yii::t('app', 'Cons'),
        ];
    }
}

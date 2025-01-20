<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int $status
 * @property string $created_at
 * @property int $buyer_id
 * @property string $name
 * @property string $address
 * @property string $email
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_PROCESSING = 5;
    const STATUS_NEW = 0;
    const STATUS_DONE = 1;
    const STATUS_PAID = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELED = 4;

    public $item_string;

    public $test_number = 2.45;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'name', 'address', 'email'], 'required'],
            [['status', 'buyer_id'], 'integer'],
            [['sum'], 'number'],
            [['created_at'], 'default', 'value' => date('Y-m-d H:i:s')],
            [['buyer_id'], 'default', 'value' => 0],
            [['created_at', 'buyer_id'], 'safe'],
            [['name', 'address', 'email', 'phone','item_string'], 'string', 'max' => 255],
        ];
    }

    public function getStatusList()
    {
        return [
            self::STATUS_PROCESSING => 'В процессе',
            self::STATUS_DONE => 'Завершен',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_FAILED => 'Оплата не прошла',
            self::STATUS_NEW => 'Новый заказ',
            self::STATUS_CANCELED => 'Отменен',
        ];
    }

    public static function staticStatusList()
    {
        return [
            self::STATUS_PROCESSING => 'В процессе',
            self::STATUS_DONE => 'Завершен',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_FAILED => 'Оплата не прошла',
            self::STATUS_NEW => 'Новый заказ',
            self::STATUS_CANCELED => 'Отменен',
        ];
    }

    public function getItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getItemsTitles()
    {
        $items = OrderItem::find()->where(['order_id' => $this->id])->all();
        $string = '';
        foreach ($items as $item) {
            $string .= $item->product_title .' '. Yii::t('app','Количество:'). $item->amount .'<br>';
        }
        return $string;
    }

    public function getStatusString()
    {
        return $this->getStatusList()[$this->status];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'buyer_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => Yii::t('app', 'Статус'),
            'created_at' => Yii::t('app', 'Дата'),
            'buyer_id' =>  Yii::t('app','Client'),
            'name' => Yii::t('app', 'Full Name'),
            'address' => Yii::t('app', 'Address'),
            'email' => 'E-mail',
            'sum' => Yii::t('app', 'Sum'),
            'phone' => Yii::t('app', 'Phone'),
            'item_string'=>Yii::t('app', 'Items'),
        ];
    }
}

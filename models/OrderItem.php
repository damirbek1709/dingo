<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orderItem".
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_title
 * @property float $price
 * @property int $amount
 * @property int|null $size
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orderItem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'product_title', 'price', 'amount'], 'required'],
            [['order_id', 'product_id', 'amount'], 'integer'],
            [['price'], 'number'],
            [['product_title'], 'string', 'max' => 255],
            [['size'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'product_title' => Yii::t('app', 'Product Title'),
            'price' => Yii::t('app', 'Price'),
            'amount' => Yii::t('app', 'Amount'),
            'size' => Yii::t('app', 'Size'),
        ];
    }
}

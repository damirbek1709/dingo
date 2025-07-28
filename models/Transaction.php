<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction".
 *
 * @property int $id
 * @property int $customer_id
 * @property float $sum
 * @property int $status
 * @property string $date
 */
class Transaction extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'sum', 'status'], 'required'],
            [['customer_id', 'status'], 'integer'],
            [['sum'], 'number'],
            [['date'], 'safe'],
            [['date'], 'default', 'value' => date('Y-m-d H:i:s')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'sum' => Yii::t('app', 'Sum'),
            'status' => Yii::t('app', 'Status'),
            'date' => Yii::t('app', 'Date'),
        ];
    }
}

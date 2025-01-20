<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_tag".
 *
 * @property int $product_id
 * @property int $tag_id
 */
class ProductTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'tag_id'], 'required'],
            [['product_id', 'tag_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'tag_id' => Yii::t('app', 'Tag ID'),
        ];
    }
}

<?php

namespace app\models;

use Yii;
use sjaakp\taggable\TagBehavior;
use app\models\Product;

/**
 * This is the model class for table "tag".
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property int|null $frequency
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public function getSheet()
    {
        return $this->hasMany(ProductTag::className(), ['tag_id' => 'id']);
    }
}

<?php

namespace app\models;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "fashion".
 *
 * @property int $id
 * @property int $title
 * @property string $text
 * @property string $description
 */
class Fashion extends \yii\db\ActiveRecord
{
    public $cover;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fashion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'text','title_en','text_en'], 'required'],
            [['text'], 'string'],
            [['description','title','title_en','text_en'], 'string', 'max' => 255],
        ];
    }

    public function getWallpaper()
    {
        if (is_dir(Yii::getAlias("@webroot/images/fashion/cover/{$this->id}"))) {
            $array = FileHelper::findFiles(Yii::getAlias("@webroot/images/fashion/cover/{$this->id}"), [
                'recursive' => false,
                'except' => ['.gitignore']
            ]);
            if (!empty($array)) {
                return Url::base() . "/images/fashion/cover/{$this->id}/" . basename($array[0]);
            }
        } else {
            return Url::base() . "/images/site/template.png";
        }
    }
    public function afterSave($insert, $changedAttributes)
    {
        $old_dir =  Yii::getAlias("@webroot/images/fashion/cover/temporary/");
        $new_dir = Yii::getAlias("@webroot/images/fashion/cover/{$this->id}/");
        FileHelper::createDirectory($new_dir);      

        if ($insert) {
            if (is_dir($old_dir)) {
                rename($old_dir, $new_dir);
            }
        } else {
            if (is_dir($old_dir)) {
                FileHelper::removeDirectory(Yii::getAlias("@webroot/images/fashion/cover/{$this->id}"));
                rename($old_dir, $new_dir);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'title_en' => Yii::t('app', 'Заголовок на английском'),
            'text_en' => Yii::t('app', 'Текст на английском'),
            'text' => Yii::t('app', 'Text'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    function translate($language)
    {
        switch ($language) {
            case "en":
                if ($this->title_en != null) $this->title = $this->{"title_en"};
                else $this->title = $this->{"title"};

                if ($this->text_en != null) $this->text = $this->{"text_en"};
                else $this->text = $this->{"text"};
                break;
            default:
                $this->title = $this->{"title"};
                $this->text = $this->{"text"};
        }
    }
}

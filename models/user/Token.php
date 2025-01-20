<?php
namespace app\models\user;

use Yii;
use dektrium\user\models\Token as BaseToken;

class Token extends BaseToken
{
    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);
            $this->setAttribute('created_at', time());
            $this->setAttribute('code', rand(1000, 9999));
        }

        return $insert;
    }
}
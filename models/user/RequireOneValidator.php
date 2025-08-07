<?php
namespace app\models\user; // adjust namespace as needed

use yii\validators\Validator;

class RequireOneValidator extends Validator
{
    public $attributes = [];
    
    public function validateAttribute($model, $attribute)
    {
        $isEmpty = true;
        foreach ($this->attributes as $attr) {
            if (!empty($model->$attr)) {
                $isEmpty = false;
                break;
            }
        }
        
        if ($isEmpty) {
            foreach ($this->attributes as $attr) {
                $model->addError($attr, 'At least one of these fields must be filled.');
            }
        }
    }
}
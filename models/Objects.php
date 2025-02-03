<?php
namespace app\models;

use yii\base\Model;

class Objects extends Model
{
    public $id;
    public $user_id;
    public $type;
    public $name;
    public $city;
    public $address;
    public $currency;
    public $features;
    public $phone;
    public $site;
    public $check_in;
    public $check_out;
    public $reception;
    public $description;
    public $lat;
    public $lon;
    public $email;
    public $uploadImages;

    public function rules()
    {
        return [
            [['id', 'type', 'city', 'address', 'currency', 'features', 'phone', 'site', 
              'check_in', 'check_out', 'reception', 'description', 'lat', 'lon', 'email','name','uploadImages','user_id'], 'safe'],
            [['email'], 'email'], // Validate email format
            [['phone'], 'match', 'pattern' => '/^\+?[0-9 ]{7,15}$/'], // Phone validation
            [['lat', 'lon'], 'number'], // Latitude and longitude should be numeric
            [['description'], 'string', 'max' => 1000], // Limit description length
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'city' => 'City',
            'address' => 'Address',
            'currency' => 'Currency',
            'features' => 'Features',
            'phone' => 'Phone',
            'site' => 'Website',
            'check_in' => 'Check-in Time',
            'check_out' => 'Check-out Time',
            'reception' => 'Reception Hours',
            'description' => 'Description',
            'lat' => 'Latitude',
            'lon' => 'Longitude',
            'email' => 'Email',
        ];
    }
}

?>
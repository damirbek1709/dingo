<?php
namespace app\models\user;

use dektrium\user\models\UserSearch as BaseUser;
use yii\data\ActiveDataProvider;

class UserSearch extends BaseUser
{

    public $name;
    public $phone;
    
    public function rules()
    {
        return [
            'fieldsSafe' => [['id', 'username', 'email', 'registration_ip', 'created_at', 'last_login_at','name','phone'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
            'lastloginDefault' => ['last_login_at', 'default', 'value' => null],
        ];
    }


    public function search($params)
    {
        $query = $this->finder->getUserQuery()->alias('u');
        $query->joinWith(['authAssignments aa']);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $modelClass = $query->modelClass;
        $table_name = $modelClass::tableName();

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', $table_name . '.created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere(['like', $table_name . '.username', $this->username])
            ->andFilterWhere(['like', $table_name . '.email', $this->email])
            ->andFilterWhere(['like', $table_name . '.name', $this->name])
            ->andFilterWhere(['like', $table_name . '.phone', $this->phone])
            ->andFilterWhere([$table_name . '.id' => $this->id])
            ->andFilterWhere([$table_name . 'registration_ip' => $this->registration_ip]);

        return $dataProvider;
    }

}
?>
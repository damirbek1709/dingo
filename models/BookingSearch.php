<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Booking;

/**
 * BookingSearch represents the model behind the search form of `app\models\Booking`.
 */
class BookingSearch extends Booking
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'room_id', 'status', 'cancellation_type', 'user_id'], 'integer'],
            [['tariff_id', 'guest_email', 'guest_phone', 'guest_name', 'date_from', 'date_to', 'other_guests', 'special_comment'], 'safe'],
            [['sum', 'cancellation_penalty_sum'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Booking::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'object_id' => $this->object_id,
            'room_id' => $this->room_id,
            'sum' => $this->sum,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'status' => $this->status,
            'cancellation_type' => $this->cancellation_type,
            'cancellation_penalty_sum' => $this->cancellation_penalty_sum,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'tariff_id', $this->tariff_id])
            ->andFilterWhere(['like', 'guest_email', $this->guest_email])
            ->andFilterWhere(['like', 'guest_phone', $this->guest_phone])
            ->andFilterWhere(['like', 'guest_name', $this->guest_name])
            ->andFilterWhere(['like', 'other_guests', $this->other_guests])
            ->andFilterWhere(['like', 'special_comment', $this->special_comment]);

        return $dataProvider;
    }
}

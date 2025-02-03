<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RoomCat;

/**
 * RoomCatSearch represents the model behind the search form of `app\models\RoomCat`.
 */
class RoomCatSearch extends RoomCat
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'guest_amount', 'similar_room_amount', 'bathroom', 'balcony', 'air_cond', 'kitchen'], 'integer'],
            [['title', 'title_en', 'title_ky'], 'safe'],
            [['area', 'base_price', 'img'], 'number'],
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
        $query = RoomCat::find();

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
            'guest_amount' => $this->guest_amount,
            'similar_room_amount' => $this->similar_room_amount,
            'area' => $this->area,
            'bathroom' => $this->bathroom,
            'balcony' => $this->balcony,
            'air_cond' => $this->air_cond,
            'kitchen' => $this->kitchen,
            'base_price' => $this->base_price,
            'img' => $this->img,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'title_en', $this->title_en])
            ->andFilterWhere(['like', 'title_ky', $this->title_ky]);

        return $dataProvider;
    }
}

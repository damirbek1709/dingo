<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Feedback;

/**
 * FeedbackSearch represents the model behind the search form of `app\models\Feedback`.
 */
class FeedbackSearch extends Feedback
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'general', 'cleaning', 'location', 'room', 'meal', 'hygien', 'price_quality', 'service', 'wifi', 'user_id'], 'integer'],
            [['pos', 'cons'], 'safe'],
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
        $query = Feedback::find();

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
            'general' => $this->general,
            'cleaning' => $this->cleaning,
            'location' => $this->location,
            'room' => $this->room,
            'meal' => $this->meal,
            'hygien' => $this->hygien,
            'price_quality' => $this->price_quality,
            'service' => $this->service,
            'wifi' => $this->wifi,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'pos', $this->pos])
            ->andFilterWhere(['like', 'cons', $this->cons]);

        return $dataProvider;
    }
}

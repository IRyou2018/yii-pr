<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PeerReview;

/**
 * PeerReviewSearch represents the model behind the search form of `common\models\PeerReview`.
 */
class PeerReviewSearch extends PeerReview
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'marker_student_id', 'individual_assessment_id'], 'integer'],
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
        $query = PeerReview::find();

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
            'marker_student_id' => $this->marker_student_id,
            'individual_assessment_id' => $this->individual_assessment_id,
        ]);

        return $dataProvider;
    }
}
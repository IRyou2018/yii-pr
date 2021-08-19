<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use Yii;

/**
 * LecturerAssessmentSearch represents the model behind the search form of `common\models\LecturerAssessment`.
 */
class CoordinatorsSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
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
        $query = User::find();

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

        $user_id = Yii::$app->user->id;

        // grid filtering conditions
        $query->andFilterWhere([
            '<>','id', $user_id,
            'status' => self::STATUS_ACTIVE,
            'type' => self::Type_Lecturer
        ]);

        return $dataProvider;
    }
}

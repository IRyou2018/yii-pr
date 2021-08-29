<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Grade;

/**
 * GradeSearch represents the model behind the search form of `common\models\Grade`.
 */
class GradeSearch extends Grade
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'weight', 'min_mark', 'max_mark'], 'integer'],
            [['grade'], 'safe'],
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
        $query = Grade::find();

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
            'weight' => $this->weight,
            'min_mark' => $this->min_mark,
            'max_mark' => $this->max_mark,
        ]);

        $query->andFilterWhere(['like', 'grade', $this->grade]);

        return $dataProvider;
    }
}

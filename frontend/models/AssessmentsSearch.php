<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Assessments;
use Yii;

/**
 * AssessmentsSearch represents the model behind the search form of `common\models\Assessments`.
 */
class AssessmentsSearch extends Assessments
{

    const UNCOMPLETE = 0;
    const COMPLETE = 1;

    const INACTIVE = 0;
    const ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'assessment_type', 'deadline', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name'], 'safe'],
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
        $query = Assessments::find();

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
            'assessment_type' => $this->assessment_type,
            'deadline' => $this->deadline,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    /**
     * Search for current year Assessments.
     *
     * @param 
     *
     * @return ActiveDataProvider
     */
    public function getCurrentYearAssessment()
    {
        $year = date("Y",strtotime("-1 year"));
        $date = "$year-09-01 00:00:00";

        $query = Assessments::find()
            ->join('INNER JOIN', 'lecturer_assessment as la', 'la.assessment_id = assessments.id')
            ->where('la.lecturer_id = :user')
            ->andWhere('assessments.deadline >= :date')
            ->addParams([
                ':user' => Yii::$app->user->id,
                ':date' => $date,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>false
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * Search for current year Assessments.
     *
     * @param 
     *
     * @return ActiveDataProvider
     */
    public function getArchivedAssessment()
    {
        $year = date("Y",strtotime("-1 year"));
        $date = "$year-09-01 00:00:00";

        $query = Assessments::find()
            ->join('INNER JOIN', 'lecturer_assessment as la', 'la.assessment_id = assessments.id')
            ->where('la.lecturer_id = :user')
            ->andWhere('assessments.deadline < :date')
            ->addParams([
                ':user' => Yii::$app->user->id,
                ':date' => $date,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>false
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * Search for current year Assessments.
     *
     * @param 
     *
     * @return ActiveDataProvider
     */
    public function searchFeedbacks()
    {
        $query = Assessments::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>false
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}

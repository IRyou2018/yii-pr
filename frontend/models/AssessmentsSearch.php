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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchByLecturerID($params)
    {
        $query = Assessments::find()
            ->join('INNER JOIN', 'lecturer_assessment as la', 'la.assessment_id = assessments.id')
            ->where(['la.lecturer_id' => Yii::$app->user->id,]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere([
            'id' => $this->id,
            'assessment_type' => $this->assessment_type,
            'deadline' => $this->deadline,
            'active' => $this->active,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    /**
     * Get uncompleted assessment list
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchUncompleted()
    {
        $query = Assessments::find()
            ->join('INNER JOIN', 'group_info as gi', 'gi.assessment_id = assessments.id')
            ->join('INNER JOIN', 'peer_assessment as ps', 'ps.group_id = gi.id')
            ->where([
                'assessments.active' => self::ACTIVE,
                'ps.completed' => self::UNCOMPLETE,
                'ps.student_id' => Yii::$app->user->id])
            ->union(
                Assessments::find()
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'peer_review as pr', 'pr.individual_assessment_id = ia.id')
                ->where([
                    'assessments.active' => self::ACTIVE,
                    'pr.completed' => self::UNCOMPLETE,
                    'pr.marker_student_id' => Yii::$app->user->id])
            );

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>false
        ]);

        return $dataProvider;
    }

    /**
     * Get completed assessment list
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchCompleted()
    {
        $query = Assessments::find()
            ->join('INNER JOIN', 'group_info as gi', 'gi.assessment_id = assessments.id')
            ->join('INNER JOIN', 'peer_assessment as ps', 'ps.group_id = gi.id')
            ->where([
                'assessments.active' => self::ACTIVE,
                'ps.completed' => self::COMPLETE,
                'ps.student_id' => Yii::$app->user->id])
            ->union(
                Assessments::find()
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'peer_review as pr', 'pr.individual_assessment_id = ia.id')
                ->where([
                    'assessments.active' => self::ACTIVE,
                    'pr.completed' => self::COMPLETE,
                    'pr.marker_student_id' => Yii::$app->user->id])
            );

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);

        return $dataProvider;
    }

    /**
     * Get feedbacks
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchFeedbacks()
    {
        $query = Assessments::find()
            ->join('INNER JOIN', 'group_info as gi', 'gi.assessment_id = assessments.id')
            ->join('INNER JOIN', 'peer_assessment as ps', 'ps.group_id = gi.id')
            ->where([
                'assessments.active' => self::ACTIVE,
                'ps.completed' => self::COMPLETE,
                'ps.student_id' => Yii::$app->user->id])
            ->union(
                Assessments::find()
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'peer_review as pr', 'pr.individual_assessment_id = ia.id')
                ->where([
                    'assessments.active' => self::ACTIVE,
                    'pr.completed' => self::COMPLETE,
                    'pr.marker_student_id' => Yii::$app->user->id])
            );

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);

        return $dataProvider;
    }
}

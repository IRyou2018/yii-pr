<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class StudentModel extends Model
{
    const UNCOMPLETE = 0;
    const COMPLETED = 1;

    const INACTIVE = 0;
    const ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * Get uncomplete / completed assessment list
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchAssessment($status)
    {
        $data = (new \Yii\db\Query())
            ->select('ps.id as id, assessments.id as assessment_id, assessments.name, assessments.deadline')
            ->from('assessments')
            ->join('INNER JOIN', 'group_info as gi', 'gi.assessment_id = assessments.id')
            ->join('INNER JOIN', 'peer_assessment as ps', 'ps.group_id = gi.id')
            ->where(['assessments.active' => self::ACTIVE])
            ->andWhere('ps.completed = :status')
            ->andWhere('ps.student_id = :user_id')
            ->addParams([
                ':status' => $status, 
                ':user_id' => Yii::$app->user->id
                ])
            ->union(
                (new \Yii\db\Query())
                ->select('pr.id as id, assessments.id as assessment_id, assessments.name, assessments.deadline')
                ->from('assessments')
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'peer_review as pr', 'pr.individual_assessment_id = ia.id')
                ->where(['assessments.active' => self::ACTIVE])
                ->andWhere('pr.completed = :status')
                ->andWhere('pr.marker_student_id = :user_id')
                ->addParams([
                    ':status' => $status, 
                    ':user_id' => Yii::$app->user->id
                    ])
            )
            ->all();

        return $data;
    }
}

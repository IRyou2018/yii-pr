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
            ->select('gsi.id as id, gsi.completed, assessments.id as assessment_id, assessments.assessment_type, assessments.name, assessments.deadline')
            ->from('assessments')
            ->join('INNER JOIN', 'group_assessment as ga', 'ga.assessment_id = assessments.id')
            ->join('INNER JOIN', 'group_student_info as gsi', 'gsi.group_id = ga.id')
            ->where(['assessments.active' => self::ACTIVE])
            ->andWhere('gsi.completed = :status')
            ->andWhere('gsi.student_id = :user_id')
            ->addParams([
                ':status' => $status, 
                ':user_id' => Yii::$app->user->id
                ])
            ->union(
                (new \Yii\db\Query())
                ->select('msi.id as id, msi.completed, assessments.id as assessment_id, assessments.assessment_type, assessments.name, assessments.deadline')
                ->from('assessments')
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'marker_student_info as msi', 'msi.individual_assessment_id = ia.id')
                ->where(['assessments.active' => self::ACTIVE])
                ->andWhere('msi.completed = :status')
                ->andWhere('msi.marker_student_id = :user_id')
                ->addParams([
                    ':status' => $status, 
                    ':user_id' => Yii::$app->user->id
                    ])
            )
            ->all();

        return $data;
    }
}

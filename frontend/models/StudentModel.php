<?php

namespace frontend\models;

use common\models\Assessments;
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

    const UNMARK = 0;
    const MARKED = 1;

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
        $year = date("Y",strtotime("-1 year"));
        $date = "$year-09-01 00:00:00";

        $data = (new \Yii\db\Query())
            ->select('gsi.id as id, gsi.completed, assessments.id as assessment_id, assessments.assessment_type, assessments.name, assessments.deadline')
            ->from('assessments')
            ->join('INNER JOIN', 'group_assessment as ga', 'ga.assessment_id = assessments.id')
            ->join('INNER JOIN', 'group_student_info as gsi', 'gsi.group_id = ga.id')
            ->where(['assessments.active' => self::ACTIVE])
            ->andWhere('gsi.completed = :status')
            ->andWhere('gsi.student_id = :user_id')
            ->andWhere('assessments.deadline >= :date')
            ->addParams([
                ':status' => $status, 
                ':user_id' => Yii::$app->user->id,
                ':date' => $date,
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
                ->andWhere('assessments.deadline >= :date')
                ->addParams([
                    ':status' => $status, 
                    ':user_id' => Yii::$app->user->id,
                    ':date' => $date,
                    ])
            )
            ->all();

        return $data;
    }

    /**
     * Get archived assessment list
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchArchivedAssessment($status)
    {
        $year = date("Y",strtotime("-1 year"));
        $date = "$year-09-01 00:00:00";

        $data = (new \Yii\db\Query())
            ->select('gsi.id as id, gsi.completed, assessments.id as assessment_id, assessments.assessment_type, assessments.name, assessments.deadline')
            ->from('assessments')
            ->join('INNER JOIN', 'group_assessment as ga', 'ga.assessment_id = assessments.id')
            ->join('INNER JOIN', 'group_student_info as gsi', 'gsi.group_id = ga.id')
            ->where(['assessments.active' => self::ACTIVE])
            ->andWhere(['gsi.completed' => self::COMPLETED])
            ->andWhere('gsi.student_id = :user_id')
            ->andWhere('assessments.deadline < :date')
            ->addParams([
                ':user_id' => Yii::$app->user->id,
                ':date' => $date,
                ])
            ->union(
                (new \Yii\db\Query())
                ->select('msi.id as id, msi.completed, assessments.id as assessment_id, assessments.assessment_type, assessments.name, assessments.deadline')
                ->from('assessments')
                ->join('INNER JOIN', 'individual_assessment as ia', 'ia.assessment_id = assessments.id')
                ->join('INNER JOIN', 'marker_student_info as msi', 'msi.individual_assessment_id = ia.id')
                ->where(['assessments.active' => self::ACTIVE])
                ->andWhere(['msi.completed' => self::COMPLETED])
                ->andWhere('msi.marker_student_id = :user_id')
                ->andWhere('assessments.deadline < :date')
                ->addParams([
                    ':user_id' => Yii::$app->user->id,
                    ':date' => $date,
                    ])
            )
            ->all();

        return $data;
    }

    /**
     * Get feedback list
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function getFeedback()
    {

        $data = (new \Yii\db\Query())
            ->select('gsi.id as id, gsi.mark as mark, grade.grade as grade, assessments.id as assessment_id, assessments.name as name, assessments.deadline as deadline')
            ->from('assessments')
            ->join('INNER JOIN', 'group_assessment as ga', 'ga.assessment_id = assessments.id')
            ->join('INNER JOIN', 'group_student_info as gsi', 'gsi.group_id = ga.id')
            ->join('LEFT OUTER JOIN', 'grade', 'gsi.mark >= grade.min_mark and gsi.mark < grade.max_mark')
            ->where(['assessments.active' => self::ACTIVE])
            ->andWhere(['gsi.marked' => self::MARKED])
            ->andWhere('gsi.student_id = :user_id')
            ->addParams([
                ':user_id' => Yii::$app->user->id,
                ])
            ->union(
                (new \Yii\db\Query())
                ->select('ia.id as id, ia.mark_value as mark, grade.grade as grade, assessments.id as assessment_id, assessments.name as name, assessments.deadline as deadline')
                ->from('individual_assessment as ia')
                ->join('INNER JOIN', 'assessments', 'ia.assessment_id = assessments.id')
                ->join('LEFT OUTER JOIN', 'grade', 'ia.mark_value >= grade.min_mark and ia.mark_value < grade.max_mark')
                ->where(['assessments.active' => self::ACTIVE])
                ->andWhere(['ia.marked' => self::MARKED])
                ->andWhere('ia.student_id = :user_id')
                ->addParams([
                    ':user_id' => Yii::$app->user->id,
                    ])
            )
            ->all();

        return $data;
    }

    public function getGroupFeedback($id, $group_id)
    {
        $data = (new \Yii\db\Query())
        ->select('gaf.item_id, gaf.mark, gaf.comment')
        ->from('group_assessment_feedback as gaf')
        ->join('INNER JOIN', 'items', 'gaf.item_id = items.id and items.item_type = 0')
        ->where('gaf.group_id = :group_id')
        ->addParams([
            ':id' => $id,
            ':group_id' => $group_id,
            ])
        ->union(
            (new \Yii\db\Query())
            ->select('gaf.item_id, gaf.mark, comment')
            ->from('group_assessment_feedback as gaf')
            ->join('INNER JOIN', 'items', 'gaf.item_id = items.id and items.item_type = 1')
            ->where('gaf.group_id = :group_id')
            ->addParams([
                ':group_id' => $group_id,
                ])
            )
        ->all();

        return $data;
    }

    public function getIndividualFeedback($id)
    {
        $data = (new \Yii\db\Query())
        ->select('iaf.item_id, iaf.mark, iaf.comment')
        ->from('individual_assessment_feedback as iaf')
        ->where('iaf.individual_assessment_id = :id')
        ->addParams([
            ':id' => $id,
            ])
        ->all();

        return $data;
    }
}

<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class StudentModel extends Model
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
     * Get peer assessment id.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getPeerAssessmentId($id)
    {

        $peerAssessmentID = (new \Yii\db\Query())
                    ->select('peer_assessment.id')
                    ->from('peer_assessment')
                    ->join('INNER JOIN', 'group_info as gi', 'peer_assessment.group_id = gi.id')
                    ->join('INNER JOIN', 'assessments', 'gi.assessment_id = assessments.id')
                    ->where('assessments.id = :assessment_id')
                    ->andWhere('peer_assessment.student_id = :user_id')
                    ->addParams([
                        ':assessment_id' => $id, 
                        ':user_id' => Yii::$app->user->id
                        ])
                    ->scalar();

        echo "<pre>";
        print_r($peerAssessmentID);
        echo "</pre>";
        exit;
        return $peerAssessmentID;
    }

    /**
     * Get peer review id.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getPeerReviewId($id)
    {

        $peerReviewID = (new \Yii\db\Query())
                    ->select('peer_review.id')
                    ->from('peer_review')
                    ->join('INNER JOIN', 'individual_assessment', 'peer_review.individual_assessment_id = individual_assessment.id')
                    ->join('INNER JOIN', 'assessments', 'individual_assessment.assessment_id = assessments.id')
                    ->where('assessments.id = :assessment_id')
                    ->andWhere('peer_review.marker_student_id = :user_id')
                    ->addParams([
                        ':assessment_id' => $id, 
                        ':user_id' => Yii::$app->user->id
                        ])
                    ->scalar();

        return $peerReviewID;
    }
}

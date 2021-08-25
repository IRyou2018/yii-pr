<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "marker_student_info".
 *
 * @property int $id
 * @property int $marker_student_id
 * @property int $individual_assessment_id
 * @property int|null $completed
 *
 * @property IndividualAssessment $individualAssessment
 * @property IndividualAssessmentDetail[] $individualAssessmentDetails
 * @property IndividualFeedback[] $individualFeedbacks
 * @property User $markerStudent
 */
class MarkerStudentInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'marker_student_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['marker_student_id', 'individual_assessment_id', 'completed'], 'required'],
            [['marker_student_id', 'individual_assessment_id', 'completed'], 'integer'],
            [['individual_assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => IndividualAssessment::className(), 'targetAttribute' => ['individual_assessment_id' => 'id']],
            [['marker_student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['marker_student_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'marker_student_id' => 'Marker Student ID',
            'individual_assessment_id' => 'Individual Assessment ID',
            'completed' => 'Completed',
        ];
    }

    /**
     * Gets query for [[IndividualAssessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessment()
    {
        return $this->hasOne(IndividualAssessment::className(), ['id' => 'individual_assessment_id']);
    }

    /**
     * Gets query for [[IndividualAssessmentDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessmentDetails()
    {
        return $this->hasMany(IndividualAssessmentDetail::className(), ['marker_student_info_id' => 'id']);
    }

    /**
     * Gets query for [[IndividualFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualFeedbacks()
    {
        return $this->hasMany(IndividualFeedback::className(), ['marker_student_info_id' => 'id']);
    }

    /**
     * Gets query for [[MarkerStudent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMarkerStudent()
    {
        return $this->hasOne(User::className(), ['id' => 'marker_student_id']);
    }
}

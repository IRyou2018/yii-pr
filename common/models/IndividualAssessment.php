<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "individual_assessment".
 *
 * @property int $id
 * @property int $student_id
 * @property int $student_number
 * @property int $mark_value
 * @property int|null $marked
 * @property string|null $file_path
 * @property int $assessment_id
 *
 * @property Assessments $assessment
 * @property PeerReview[] $peerReviews
 * @property User $student
 */
class IndividualAssessment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'individual_assessment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'assessment_id'], 'required'],
            [['student_id', 'student_number', 'mark_value', 'marked', 'assessment_id'], 'integer'],
            [['file_path'], 'string', 'max' => 255],
            [['assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Assessments::className(), 'targetAttribute' => ['assessment_id' => 'id']],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['student_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'student_number' => 'Student Number', 
            'mark_value' => 'Mark Value',
            'marked' => 'Marked',
            'file_path' => 'File Path',
            'assessment_id' => 'Assessment ID',
        ];
    }

    /**
     * Gets query for [[Assessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessment()
    {
        return $this->hasOne(Assessments::className(), ['id' => 'assessment_id']);
    }

    /**
     * Gets query for [[PeerReviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerReviews()
    {
        return $this->hasMany(PeerReview::className(), ['individual_assessment_id' => 'id']);
    }

    /**
     * Gets query for [[Student]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(User::className(), ['id' => 'student_id']);
    }
}

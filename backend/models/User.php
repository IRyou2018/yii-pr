<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $username
 * @property string $first_name
 * @property string $last_name
 * @property string $matric_number
 * @property string $auth_key
 * @property string|null $password_hash
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property string $email
 * @property int $type
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Assessments[] $assessments
 * @property Assessments[] $assessments0
 * @property GroupAssessmentDetail[] $groupAssessmentDetails
 * @property GroupAssessmentFeedback[] $groupAssessmentFeedbacks
 * @property GroupStudentInfo[] $groupStudentInfos
 * @property IndividualAssessmentFeedback[] $individualAssessmentFeedbacks
 * @property IndividualAssessment[] $individualAssessments
 * @property LecturerAssessment[] $lecturerAssessments
 * @property MarkerStudentInfo[] $markerStudentInfos
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'matric_number', 'auth_key', 'email', 'type', 'created_at', 'updated_at'], 'required'],
            [['type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'verification_token', 'email'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 60],
            [['matric_number'], 'string', 'max' => 15],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'unique'],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'matric_number' => 'Matric Number',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'verification_token' => 'Verification Token',
            'email' => 'Email',
            'type' => 'Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Assessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessments()
    {
        return $this->hasMany(Assessments::className(), ['created_by' => 'id']);
    }

    /**
     * Gets query for [[Assessments0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessments0()
    {
        return $this->hasMany(Assessments::className(), ['updated_by' => 'id']);
    }

    /**
     * Gets query for [[GroupAssessmentDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentDetails()
    {
        return $this->hasMany(GroupAssessmentDetail::className(), ['work_student_id' => 'id']);
    }

    /**
     * Gets query for [[GroupAssessmentFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentFeedbacks()
    {
        return $this->hasMany(GroupAssessmentFeedback::className(), ['student_id' => 'id']);
    }

    /**
     * Gets query for [[GroupStudentInfos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupStudentInfos()
    {
        return $this->hasMany(GroupStudentInfo::className(), ['student_id' => 'id']);
    }

    /**
     * Gets query for [[IndividualAssessmentFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessmentFeedbacks()
    {
        return $this->hasMany(IndividualAssessmentFeedback::className(), ['student_id' => 'id']);
    }

    /**
     * Gets query for [[IndividualAssessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessments()
    {
        return $this->hasMany(IndividualAssessment::className(), ['student_id' => 'id']);
    }

    /**
     * Gets query for [[LecturerAssessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLecturerAssessments()
    {
        return $this->hasMany(LecturerAssessment::className(), ['lecturer_id' => 'id']);
    }

    /**
     * Gets query for [[MarkerStudentInfos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMarkerStudentInfos()
    {
        return $this->hasMany(MarkerStudentInfo::className(), ['marker_student_id' => 'id']);
    }
}

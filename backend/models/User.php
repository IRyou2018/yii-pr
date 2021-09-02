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
            [['first_name', 'last_name', 'matric_number', 'email', 'type'], 'required'],
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
}

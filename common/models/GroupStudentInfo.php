<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_student_info".
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $completed
 * @property int|null $mark
 * @property int|null $marked
 * @property int $group_id
 *
 * @property GroupAssessment $group
 * @property GroupAssessmentDetail[] $groupAssessmentDetails
 * @property GroupAssessmentFeedback[] $groupAssessmentFeedbacks
 * @property User $student
 */
class GroupStudentInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_student_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'group_id'], 'required'],
            [['student_id', 'completed', 'mark', 'marked', 'group_id'], 'integer'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupAssessment::className(), 'targetAttribute' => ['group_id' => 'id']],
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
            'completed' => 'Completed',
            'mark' => 'Mark',
            'marked' => 'Marked',
            'group_id' => 'Group ID',
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(GroupAssessment::className(), ['id' => 'group_id']);
    }

    /**
     * Gets query for [[GroupAssessmentDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentDetails()
    {
        return $this->hasMany(GroupAssessmentDetail::className(), ['group_student_Info_id' => 'id']);
    }

    /**
     * Gets query for [[GroupAssessmentFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentFeedbacks()
    {
        return $this->hasMany(GroupAssessmentFeedback::className(), ['group_student_Info_id' => 'id']);
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

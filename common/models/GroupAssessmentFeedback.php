<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_assessment_feedback".
 *
 * @property int $id
 * @property int $student_id
 * @property int $mark
 * @property string|null $comment
 * @property int $item_id
 * @property int|null $group_student_Info_id
 *
 * @property GroupStudentInfo $groupStudentInfo
 * @property Items $item
 * @property User $student
 */
class GroupAssessmentFeedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_assessment_feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'mark', 'item_id'], 'required'],
            [['student_id', 'mark', 'item_id', 'group_student_Info_id'], 'integer'],
            [['comment'], 'string'],
            [['group_student_Info_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupStudentInfo::className(), 'targetAttribute' => ['group_student_Info_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['student_id' => 'id']],
            [['mark'], 'validateMark'],
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
            'mark' => 'Mark',
            'comment' => 'Comment',
            'item_id' => 'Item ID',
            'group_student_Info_id' => 'Group Student  Info ID',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        return $scenarios;
    }

    /**
     * Gets query for [[GroupStudentInfo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupStudentInfo()
    {
        return $this->hasOne(GroupStudentInfo::className(), ['id' => 'group_student_Info_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
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

    public function validateMark($attribute, $params) {
        
        if ($this->mark > $this->item->max_mark_value) {
            $this->addError($attribute, 'Mark must be less than or equal to Max Mark.');
            return false;
        }
    }
}

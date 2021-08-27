<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_assessment_detail".
 *
 * @property int $id
 * @property int $work_student_id
 * @property int $mark
 * @property string|null $comment
 * @property int $contribution
 * @property int $item_id
 * @property int|null $group_student_Info_id
 *
 * @property GroupStudentInfo $groupStudentInfo
 * @property Items $item
 * @property User $workStudent
 */
class GroupAssessmentDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_assessment_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id'], 'required'],
            [['work_student_id', 'mark', 'contribution', 'item_id', 'group_student_Info_id'], 'integer'],
            [['comment'], 'string'],
            [['group_student_Info_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupStudentInfo::className(), 'targetAttribute' => ['group_student_Info_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['work_student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['work_student_id' => 'id']],
            [['mark'], 'validateMark'],
            // [['contribution'], 'validateContribution'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'work_student_id' => 'Work Student ID',
            'mark' => 'Mark',
            'comment' => 'Comment',
            'contribution' => 'Contribution',
            'item_id' => 'Item ID',
            'group_student_Info_id' => 'Group Student  Info ID',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        $scenarios['contribution'] = ['contribution'];
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
     * Gets query for [[WorkStudent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkStudent()
    {
        return $this->hasOne(User::className(), ['id' => 'work_student_id']);
    }

    /**
     * Gets student's full name.
     *
     * @return string
     */
    public function getStudentName()
    {
        return $this->workStudent->first_name . " " . $this->workStudent->last_name;
    }

    /**
     * Validate input mark value which should be less than or equal to max mark value.
     *
     * @return \yii\db\ActiveQuery
     */
    public function validateMark($attribute, $params) {
        
        if ($this->mark > $this->item->max_mark_value) {
            $this->addError($attribute, 'Mark must be less than or equal to Max Mark.');
            return false;
        }
    }

}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_assessment_feedback".
 *
 * @property int $id
 * @property int|null $student_id
 * @property int $mark
 * @property string|null $comment
 * @property int $item_id
 * @property int|null $group_id
 *
 * @property GroupAssessment $group
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
            [['mark', 'item_id'], 'required'],
            [['student_id', 'mark', 'item_id', 'group_id'], 'integer'],
            [['comment'], 'string'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupAssessment::className(), 'targetAttribute' => ['group_id' => 'id']],
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
            'group_id' => 'Group ID',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        return $scenarios;
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

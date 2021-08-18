<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "peer_assessment".
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $completed
 * @property string|null $mark
 * @property int|null $marked
 * @property int $group_id
 *
 * @property GroupInfo $group
 * @property User $student
 */
class PeerAssessment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'peer_assessment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'group_id'], 'required'],
            [['student_id', 'completed', 'marked', 'group_id'], 'integer'],
            [['mark'], 'string', 'max' => 5],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => GroupInfo::className(), 'targetAttribute' => ['group_id' => 'id']],
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
        return $this->hasOne(GroupInfo::className(), ['id' => 'group_id']);
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

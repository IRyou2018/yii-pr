<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "individual_assessment_feedback".
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $mark
 * @property string|null $comment
 * @property int $item_id
 * @property int $individual_assessment_id
 *
 * @property IndividualAssessment $individualAssessment
 * @property Items $item
 * @property User $student
 */
class IndividualAssessmentFeedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'individual_assessment_feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'item_id', 'individual_assessment_id'], 'required'],
            [['student_id', 'mark', 'item_id', 'individual_assessment_id'], 'integer'],
            [['comment'], 'string'],
            [['individual_assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => IndividualAssessment::className(), 'targetAttribute' => ['individual_assessment_id' => 'id']],
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
            'individual_assessment_id' => 'Individual Assessment ID',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        return $scenarios;
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

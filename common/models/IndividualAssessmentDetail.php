<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "individual_assessment_detail".
 *
 * @property int $id
 * @property int $item_id
 * @property int $mark
 * @property string $comment
 * @property int|null $marker_student_info_id
 *
 * @property Items $item
 * @property MarkerStudentInfo $markerStudentInfo
 */
class IndividualAssessmentDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'individual_assessment_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'mark', 'comment'], 'required'],
            [['item_id', 'mark', 'marker_student_info_id'], 'integer'],
            [['comment'], 'string'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['marker_student_info_id'], 'exist', 'skipOnError' => true, 'targetClass' => MarkerStudentInfo::className(), 'targetAttribute' => ['marker_student_info_id' => 'id']],
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
            'item_id' => 'Item ID',
            'mark' => 'Mark',
            'comment' => 'Comment',
            'marker_student_info_id' => 'Marker Student Info ID',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        return $scenarios;
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
     * Gets query for [[MarkerStudentInfo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMarkerStudentInfo()
    {
        return $this->hasOne(MarkerStudentInfo::className(), ['id' => 'marker_student_info_id']);
    }

    public function validateMark($attribute, $params) {
        
        if ($this->mark > $this->item->max_mark_value) {
            $this->addError($attribute, 'Mark must be less than or equal to Max Mark.');
            return false;
        }
    }
}

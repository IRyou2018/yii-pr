<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "items".
 *
 * @property int $id
 * @property string $name
 * @property int $max_mark_value
 * @property int $item_type
 * @property int $section_id
 *
 * @property GroupAssessmentDetail[] $groupAssessmentDetails
 * @property GroupAssessmentFeedback[] $groupAssessmentFeedbacks
 * @property IndividualAssessmentDetail[] $individualAssessmentDetails
 * @property IndividualAssessmentFeedback[] $individualAssessmentFeedbacks
 * @property Rubrics[] $rubrics
 * @property Sections $section
 */
class Items extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'max_mark_value', 'item_type'], 'required'],
            [['max_mark_value', 'item_type', 'section_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['section_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sections::className(), 'targetAttribute' => ['section_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'max_mark_value' => 'Max Mark Value',
            'item_type' => 'Item Type',
            'section_id' => 'Section ID',
        ];
    }

    /**
     * Gets query for [[GroupAssessmentDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentDetails()
    {
        return $this->hasMany(GroupAssessmentDetail::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[GroupAssessmentFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupAssessmentFeedbacks()
    {
        return $this->hasMany(GroupAssessmentFeedback::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[IndividualAssessmentDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessmentDetails()
    {
        return $this->hasMany(IndividualAssessmentDetail::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[IndividualAssessmentFeedbacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndividualAssessmentFeedbacks()
    {
        return $this->hasMany(IndividualAssessmentFeedback::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Rubrics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRubrics()
    {
        return $this->hasMany(Rubrics::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Section]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSection()
    {
        return $this->hasOne(Sections::className(), ['id' => 'section_id']);
    }
}

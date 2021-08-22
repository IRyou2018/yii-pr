<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sections".
 *
 * @property int $id
 * @property string $name
 * @property int $assessment_id
 * @property int $section_type
 *
 * @property Assessments $assessment
 * @property Items[] $items
 */
class Sections extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'assessment_id', 'section_type'], 'required'],
            [['assessment_id', 'section_type'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Assessments::className(), 'targetAttribute' => ['assessment_id' => 'id']],
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
            'assessment_id' => 'Assessment ID',
            'section_type' => 'Section Type',
        ];
    }

    /**
     * Gets query for [[Assessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessment()
    {
        return $this->hasOne(Assessments::className(), ['id' => 'assessment_id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Items::className(), ['section_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStudentSections($id)
    {
        return $this->find()
            ->join('INNER JOIN', 'assessments', 'sections.assessment_id = assessments.id')
            ->where('assessments.id = :id', [':id' => $id])
            ->andWhere('sections.section_type = 0')
            ->all();
    }
}

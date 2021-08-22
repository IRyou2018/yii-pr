<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "group_info".
 *
 * @property int $id
 * @property string $name
 * @property int $group_number
 * @property string|null $mark
 * @property int|null $marked
 * @property int|null $assessment_id
 *
 * @property Assessments $assessment
 * @property PeerAssessment[] $peerAssessments
 */
class GroupInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'group_number'], 'required'],
            [['group_number', 'marked', 'assessment_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['mark'], 'string', 'max' => 5],
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
            'group_number' => 'Group Number',
            'mark' => 'Mark',
            'marked' => 'Marked',
            'assessment_id' => 'Assessment ID',
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
     * Gets query for [[PeerAssessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerAssessments()
    {
        return $this->hasMany(PeerAssessment::className(), ['group_id' => 'id']);
    }
}

<?php

namespace common\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "assessments".
 *
 * @property int $id
 * @property string $name
 * @property int $assessment_type
 * @property string $deadline
 * @property int $active
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property User $createdBy
 * @property GroupAssessment[] $groupAssessments
 * @property IndividualAssessment[] $individualAssessments
 * @property LecturerAssessment[] $lecturerAssessments
 * @property Sections[] $sections
 * @property User $updatedBy
 */
class Assessments extends \yii\db\ActiveRecord
{
    const INACTIVE = 0;
    const ACTIVE = 1;

    const GROUP = 0;
    const INDIVIDUAL = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessments';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'assessment_type', 'deadline', 'active'], 'required'],
            [['assessment_type', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['deadline'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Assessment Name',
            'assessment_type' => 'Assessment Type',
            'deadline' => 'Deadline',
            'active' => 'Visibility',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    } 
 
    /**
    * Gets query for [[GroupAssessments]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getGroupAssessments()
    {
        return $this->hasMany(GroupAssessment::className(), ['assessment_id' => 'id']);
    }

    /**
    * Gets query for [[IndividualAssessments]].
    *
    * @return \yii\db\ActiveQuery
    */
    public function getIndividualAssessments()
    {
        return $this->hasMany(IndividualAssessment::className(), ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[LecturerAssessments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLecturerAssessments()
    {
        return $this->hasMany(LecturerAssessment::className(), ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[Sections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSections()
    {
        return $this->hasMany(Sections::className(), ['assessment_id' => 'id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Customizie return text of assessment_type.
     */
    public function getAssessmentType()
    {
        switch ($this->assessment_type) {
            case self::GROUP:

                $text = "Peer Assessment";
                break;
        
            case self::INDIVIDUAL:
        
                $text = "Peer Review";
                break;
            
            default:

                $text = "(Undefined)";
                break;
        }       

        return $text;
    }

    /**
     * Customizie return text of active status.
     */
    public function getActiveStatus()
    {
        switch ($this->active) {
            case self::ACTIVE:

                $text = "Active";
                break;
        
            case self::INACTIVE:
        
                $text = "Inactive";
                break;
            
            default:

                $text = "(Undefined)";
                break;
        }       

        return $text;
    }

    /**
     * Customizie return text of active status.
     */
    public function getFinished()
    {
        return "Finished";
    }
}

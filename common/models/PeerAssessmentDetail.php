<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "peer_assessment_detail".
 *
 * @property int $id
 * @property int $work_student_id
 * @property int $mark
 * @property string|null $comment
 * @property int $contribution
 * @property int $item_id
 * @property int|null $peer_assessment_id
 *
 * @property Items $item
 * @property PeerAssessment $peerAssessment
 * @property User $workStudent
 */
class PeerAssessmentDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'peer_assessment_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['work_student_id', 'mark', 'contribution', 'item_id'], 'required'],
            [['work_student_id', 'mark', 'contribution', 'item_id', 'peer_assessment_id'], 'integer'],
            [['comment'], 'string'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['peer_assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => PeerAssessment::className(), 'targetAttribute' => ['peer_assessment_id' => 'id']],
            [['work_student_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['work_student_id' => 'id']],
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
            'peer_assessment_id' => 'Peer Assessment ID',
        ];
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
     * Gets query for [[PeerAssessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerAssessment()
    {
        return $this->hasOne(PeerAssessment::className(), ['id' => 'peer_assessment_id']);
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
}

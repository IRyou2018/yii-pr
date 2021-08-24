<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "individual_feedback".
 *
 * @property int $id
 * @property int $student_id
 * @property int $mark
 * @property string|null $comment
 * @property int $item_id
 * @property int|null $peer_review_id
 *
 * @property Items $item
 * @property PeerAssessment $peerReview
 * @property User $student
 */
class IndividualFeedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'individual_feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'item_id'], 'required'],
            [['student_id', 'mark', 'item_id', 'peer_review_id'], 'integer'],
            [['comment'], 'string'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['peer_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => PeerAssessment::className(), 'targetAttribute' => ['peer_review_id' => 'id']],
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
            'peer_review_id' => 'Peer Review ID',
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
     * Gets query for [[PeerReview]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerReview()
    {
        return $this->hasOne(PeerAssessment::className(), ['id' => 'peer_review_id']);
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

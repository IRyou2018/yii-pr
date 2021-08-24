<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "peer_review_detail".
 *
 * @property int $id
 * @property int $item_id
 * @property int $mark
 * @property string|null $comment
 * @property int|null $peer_review_id
 *
 * @property Items $item
 * @property PeerReview $peerReview
 */
class PeerReviewDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'peer_review_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['item_id', 'mark', 'comment'], 'required',
                'message'=>'Please enter a value for {attribute}.'
            ],
            [['item_id', 'mark', 'peer_review_id'], 'integer'],
            [['comment'], 'string'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['peer_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => PeerReview::className(), 'targetAttribute' => ['peer_review_id' => 'id']],
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
            'mark' => 'Proposed Mark',
            'comment' => 'Comment',
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
        return $this->hasOne(PeerReview::className(), ['id' => 'peer_review_id']);
    }

    public function validateMark($attribute, $params) {
        
        if ($this->mark > $this->item->max_mark_value) {
            $this->addError($attribute, 'Mark must be less than or equal to Max Mark.');
            return false;
        }
    }
}

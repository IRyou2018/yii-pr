<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rubrics".
 *
 * @property int $id
 * @property string $level
 * @property string $description
 * @property int $weight
 * @property int $item_id
 *
 * @property Items $item
 */
class Rubrics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rubrics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['level', 'required', 'when' => function($model) {
                return !empty($model->weight) || !empty($model->description) ;
            }, 'enableClientValidation' => false],
            ['weight', 'required', 'when' => function($model) {
                return !empty($model->level) || !empty($model->description) ;
            }, 'enableClientValidation' => false],
            ['description', 'required', 'when' => function($model) {
                return !empty($model->weight) || !empty($model->level) ;
            }, 'enableClientValidation' => false],
            [['weight', 'item_id'], 'integer'],
            [['level', 'description'], 'string', 'max' => 255],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => 'Level',
            'description' => 'Description',
            'weight' => 'Weight',
            'item_id' => 'Item ID',
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
}

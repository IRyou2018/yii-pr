<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "grade".
 *
 * @property int $id
 * @property string $grade
 * @property int $weight
 * @property int $min_mark
 * @property int $max_mark
 */
class Grade extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grade';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['grade', 'weight', 'min_mark', 'max_mark'], 'required'],
            [['weight', 'min_mark', 'max_mark'], 'integer'],
            [['grade'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'grade' => 'Grade',
            'weight' => 'Weight',
            'min_mark' => 'Min Mark',
            'max_mark' => 'Max Mark',
        ];
    }
}

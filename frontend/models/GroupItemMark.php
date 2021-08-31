<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * GroupStudent is the model behind the add-group form.
 */
class GroupItemMark extends Model
{
    public $item_max_mark;
    public $mark;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // mark, item_max_mark are required
            [['mark'], 'required'],
            [['mark', 'item_max_mark'], 'integer'],
            // validates if mark is less than or equal to item_max_mark
            [['mark'], 'validateMark'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['submit'] = ['mark'];
        return $scenarios;
    }

    public function validateMark($attribute, $params) {
        
        if ($this->mark > $this->item_max_mark) {
            $this->addError($attribute, 'Mark must be less than or equal to Max Mark.');
            return false;
        }
    }
}

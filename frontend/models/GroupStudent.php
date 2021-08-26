<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * GroupStudent is the model behind the add-group form.
 */
class GroupStudent extends Model
{
    public $first_name;
    public $last_name;
    public $matric_number;
    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // first_name, last_name, matric_number and email are required
            [['first_name', 'last_name', 'matric_number', 'email'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
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
}

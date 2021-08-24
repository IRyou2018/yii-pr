<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * LecturerAssessmentSearch represents the model behind the search form of `common\models\LecturerAssessment`.
 */
class CoordinatorsSearch extends Model
{
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const Type_Lecturer = 0;
    const Type_Student = 1;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function getCoordinators($id)
    {
        $query = User::find()
            ->Where([
            '<>','id = :id', [':id' => $id],
            'status' => self::STATUS_ACTIVE,
            'type' => self::Type_Lecturer
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {

            return $dataProvider;
        }      

        return $dataProvider;
    }
}

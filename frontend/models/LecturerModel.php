<?php

namespace frontend\models;

use common\models\PeerReview;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ContactForm is the model behind the contact form.
 */
class LecturerModel extends Model
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * Get coordinators.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getCoordinators($id)
    {
        $coorinators = (new yii\db\Query())
            ->select(["CONCAT(first_name, ' ', last_name) as name"])
            ->from('user')
            ->join('INNER JOIN', 'lecturer_assessment as ls', 'ls.lecturer_id = user.id')
            ->where('ls.lecturer_id <> :user_id')
            ->andWhere('ls.assessment_id = :assessment_id')
            ->addParams([
                ':assessment_id' => $id, 
                ':user_id' => Yii::$app->user->id
                ])
            ->all();

        return $coorinators;
    }

    /**
     * Get coordinators list.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getCoordinatorList()
    {
        $query = User::find();
        $query->where(['status' => 10, 'type' => 0]);
        $query->andWhere(['<>','id', Yii::$app->user->id]);
        $query->all();

        $coorinators = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $coorinators;
    }

    /**
     * Get group info.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getGroupInfo($id)
    {

        $groupsInfo = (new yii\db\Query())
            ->select(["COUNT(*) as toalCount,
                    Sum(completed) as completeCount,
                    ga.id,
                    ga.name,
                    ga.mark"])
            ->from('group_assessment as ga')
            ->join('INNER JOIN', 'group_student_info as gsi', 'ga.id = gsi.group_id')
            ->where('ga.assessment_id = :assessment_id')
            ->addParams([
                ':assessment_id' => $id, 
                ])
            ->groupBy('ga.id', 'ga.mark')
            ->all();

        $inconsistent = [];
        $completed = [];
        $incomplete = [];

        if(!empty($groupsInfo)) {

            foreach ($groupsInfo as $group) {
                if($group["toalCount"] > 0 && $group["completeCount"] == 0)
                {
                    array_push($incomplete,$group);		
                }
                else if ($group["toalCount"] == $group["completeCount"]) {
                    array_push($completed,$group);
                }
                else
                {
                    array_push($inconsistent,$group);
                }
            }
        }

        $groupInfo = [
            'inconsistent' => [],
            'completed' => [],
            'incomplete' => [],
        ];
        ArrayHelper::setValue($groupInfo, 'inconsistent', $inconsistent);
        ArrayHelper::setValue($groupInfo, 'completed', $completed);
        ArrayHelper::setValue($groupInfo, 'incomplete', $incomplete);

        return $groupInfo;
    }

    /**
     * Get peer coordinators.
     *
     * @param string assessment_id
     * @return peer_assessment_id
     */
    public function getStudentMarkStatus($id)
    {

        $query = (new yii\db\Query())
            ->select(["msi.id,
                    ia.marked,
                    CONCAT(u1.first_name, ' ', u1.last_name) as work_student_name,
                    CONCAT(u2.first_name, ' ', u2.last_name) as marker_student_name"])
            ->from('marker_student_info as msi')
            ->join('INNER JOIN', 'individual_assessment as ia', 'msi.individual_assessment_id = ia.id')
            ->join('LEFT OUTER JOIN', 'user u1', 'ia.student_id = u1.id')
            ->join('LEFT OUTER JOIN', 'user u2', 'msi.marker_student_id = u2.id')
            ->where('ia.assessment_id = :assessment_id')
            ->addParams([
                ':assessment_id' => $id, 
                ]);

        $individualInfo = new ActiveDataProvider([
            'query' => $query,
            'sort' =>false
        ]);

        return $individualInfo;
    }
}

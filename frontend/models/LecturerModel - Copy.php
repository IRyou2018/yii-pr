<?php

namespace frontend\models;

use common\models\GroupAssessment;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessment;
use common\models\LecturerAssessment;
use common\models\MarkerStudentInfo;
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
    const DEFAULTPASS = "00000000";
    
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
     * @param int assessment_id
     * @return coorinators
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
     * @param int assessment_id
     * @return coorinators
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
     * @param int assessment_id
     * @return array group name and marking stauts
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
     * @param int assessment_id
     * @return array student name and marking status
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

    public function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    public function registDatafromUpload($excelData, $model)
    {
        $flag = true;

        $i = 0;
        
        // For Group Assessment
        if ($model->assessment_type == 0 || $model->assessment_type == 1 || $model->assessment_type == 2) {

            $sortedData = $this->arraySort($excelData, 'Group Name', SORT_ASC);

            $temp_Group_name = '';
            $temp_Group_id = '';
            $group_number = 1;

            do {
                // Skip empty row
                if (
                    empty($sortedData[$i]['Group Name'])
                    && empty($sortedData[$i]['First Name'])
                    && empty($sortedData[$i]['Last Name'])
                    && empty($sortedData[$i]['Matriculation Number'])
                    && empty($sortedData[$i]['Email'])
                ) {

                    continue;
                } else {

                    // For same group add Student to group
                    if (!empty($temp_Group_name) && $sortedData[$i]['Group Name'] == $temp_Group_name) {

                        $modelUser = new User();

                        //Get student info by email
                        $student = $modelUser->findByEmail($sortedData[$i]['Email']);

                        $modelGroupStudentInfo = new GroupStudentInfo();
                        $modelGroupStudentInfo->group_id = $temp_Group_id;
                        $modelGroupStudentInfo->marked = 0;
                        $modelGroupStudentInfo->completed = 0;

                        // If student not exist, regist
                        if (empty($student)) {

                            $modelUser->first_name = $sortedData[$i]['First Name'];
                            $modelUser->last_name = $sortedData[$i]['Last Name'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                            $modelUser->email = $sortedData[$i]['Email'];
                            $modelUser->type = '1';
                            $modelUser->setPassword(self::DEFAULTPASS);
                            $modelUser->generateAuthKey();

                            if($flag = $modelUser->save(false)) {
                                $modelGroupStudentInfo->student_id = $modelUser->id;
                            }
                        } else {
                            $modelGroupStudentInfo->student_id = $student->id;
                        }

                        if ($flag = $modelGroupStudentInfo->save(false)) {
                        } else {
                            break;
                        }
                    } else {
                        // Regist new gourp
                        $temp_Group_name = $sortedData[$i]['Group Name'];;

                        $modelGroupInfo = new GroupAssessment();

                        $modelGroupInfo->name = $temp_Group_name;
                        $modelGroupInfo->assessment_id = $model->id;
                        $modelGroupInfo->group_number = $group_number;
                        $modelGroupInfo->marked = 0;

                        // echo '<pre>';
                        // print_r($modelGroupInfo);
                        // echo '</pre>';
                        // die;
                        if ($flag = $modelGroupInfo->save(false)) {

                            $temp_Group_id = $modelGroupInfo->id;
                            $group_number++;

                            $modelUser = new User();

                            //Get student info by email
                            $student = $modelUser->findByEmail($sortedData[$i]['Email']);

                            $modelGroupStudentInfo = new GroupStudentInfo();
                            $modelGroupStudentInfo->group_id = $temp_Group_id;
                            $modelGroupStudentInfo->marked = 0;
                            $modelGroupStudentInfo->completed = 0;
                            
                            // If student not exist, regist
                            if (empty($student)) {
                                $modelUser->first_name = $sortedData[$i]['First Name'];
                                $modelUser->last_name = $sortedData[$i]['Last Name'];
                                $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                $modelUser->email = $sortedData[$i]['Email'];
                                $modelUser->type = '1';
                                $modelUser->setPassword(self::DEFAULTPASS);
                                $modelUser->generateAuthKey();
                                
                                if($flag = $modelUser->save(false)) {
                                    $modelGroupStudentInfo->student_id = $modelUser->id;
                                }
                            } else {
                                $modelGroupStudentInfo->student_id = $student->id;
                            }

                            if ($flag = $modelGroupStudentInfo->save(false)) {
                            } else {
                                break;
                            }
                        } else {
                            $flag = false;
                            break;
                        }
                    }
                }

                $i++;
            } while ($i < count($sortedData));
        }
        // For Self Assessment
        else if ($model->assessment_type == 3) {

            $temp_student_email = '';
            $temp_individual_assessment_id = '';
            $student_number = 1;

            foreach ($excelData as $data) {
                // Skip empty row
                if (
                    empty($data['First Name'])
                    && empty($data['Last Name'])
                    && empty($data['Matriculation Number'])
                    && empty($data['Email'])
                ) {
                    continue;
                } else {
                    $temp_student_email = $data['Email'];

                    $modelUser = new User();

                    //Get student info by email
                    $student = $modelUser->findByEmail($temp_student_email);

                    $modelIndividualAssessment = new IndividualAssessment();
                    $modelIndividualAssessment->assessment_id = $model->id;
                    $modelIndividualAssessment->student_number = $student_number;
                    $modelIndividualAssessment->marked = 0;

                    // If student not exist, regist
                    if (empty($student)) {

                        $modelUser->first_name = $data['First Name'];
                        $modelUser->last_name = $data['Last Name'];
                        $modelUser->matric_number = $data['Matriculation Number'];
                        $modelUser->email = $data['Email'];
                        $modelUser->type = '1';
                        $modelUser->setPassword(self::DEFAULTPASS);
                        $modelUser->generateAuthKey();
                        
                        if ($flag = $modelUser->save(false)) {
                            $modelIndividualAssessment->student_id = $modelUser->id;
                        }
                    } else {

                        $modelIndividualAssessment->student_id = $student->id;
                    }

                    if ($flag = $modelIndividualAssessment->save(false)) {

                        $student_number++;

                        $modelMarkerStudentInfo = new MarkerStudentInfo();
                        $modelMarkerStudentInfo->individual_assessment_id = $modelIndividualAssessment->id;
                        $modelMarkerStudentInfo->completed = 0;
                        $modelMarkerStudentInfo->marker_student_id = $modelIndividualAssessment->student_id;

                        if ($flag = $modelMarkerStudentInfo->save(false)) {
                        } else {
                            break;
                        }
                    }
                }
            }
        }

        else if ($model->assessment_type == 4) {
            $sortedData = $this->arraySort($excelData, 'Email', SORT_ASC);
            $temp_student_email = '';
            $temp_individual_assessment_id = '';
            $student_number = 1;

            do {
                // Skip empty row
                if (
                    empty($sortedData[$i]['First Name'])
                    && empty($sortedData[$i]['Last Name'])
                    && empty($sortedData[$i]['Matriculation Number'])
                    && empty($sortedData[$i]['Email'])
                    && empty($sortedData[$i]['Work File'])
                    && empty($sortedData[$i]['First Name(Marker Student)'])
                    && empty($sortedData[$i]['Last Name(Marker Student)'])
                    && empty($sortedData[$i]['Matriculation Number(Marker Student)'])
                    && empty($sortedData[$i]['Email(Marker Student)'])
                ) {
                    
                    continue;
                } else {
                    
                    // For same work student, regist peer review (Marker Student)
                    if (!empty($temp_student_email) && $sortedData[$i]['Email'] == $temp_student_email) {
                        
                        $modelUser = new User();

                        //Get student info by email
                        $student = $modelUser->findByEmail($sortedData[$i]['Email(Marker Student)']);

                        $modelMarkerStudentInfo = new MarkerStudentInfo();
                        $modelMarkerStudentInfo->individual_assessment_id = $temp_individual_assessment_id;
                        $modelMarkerStudentInfo->completed = 0;

                        // If student not exist, regist
                        if (empty($student)) {
                            
                            $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                            $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                            $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                            $modelUser->type = '1';
                            $modelUser->setPassword(self::DEFAULTPASS);
                            $modelUser->generateAuthKey();

                            if($flag = $modelUser->save(false)) {
                                $modelMarkerStudentInfo->marker_student_id = $modelUser->id;
                            }
                        } else {
                            $modelMarkerStudentInfo->marker_student_id = $student->id;
                        }

                        if ($flag = $modelMarkerStudentInfo->save(false)) {
                        } else {
                            break;
                        }
                    } else {
                        
                        $temp_student_email = $sortedData[$i]['Email'];

                        $modelUser = new User();

                        //Get student info by email
                        $student = $modelUser->findByEmail($temp_student_email);

                        $modelIndividualAssessment = new IndividualAssessment();
                        $modelIndividualAssessment->file_path = $sortedData[$i]['Work File'];
                        $modelIndividualAssessment->assessment_id = $model->id;
                        $modelIndividualAssessment->student_number = $student_number;
                        $modelIndividualAssessment->marked = 0;

                        // If student not exist, regist
                        if (empty($student)) {

                            $modelUser->first_name = $sortedData[$i]['First Name'];
                            $modelUser->last_name = $sortedData[$i]['Last Name'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                            $modelUser->email = $sortedData[$i]['Email'];
                            $modelUser->type = '1';
                            $modelUser->setPassword(self::DEFAULTPASS);
                            $modelUser->generateAuthKey();
                            
                            if ($flag = $modelUser->save(false)) {
                                $modelIndividualAssessment->student_id = $modelUser->id;
                            }
                        } else {

                            $modelIndividualAssessment->student_id = $student->id;
                        }

                        if ($flag = $modelIndividualAssessment->save(false)) {
 
                            $temp_individual_assessment_id = $modelIndividualAssessment->id;
                            $student_number++;

                            $modelUser = new User();

                            // Get student info by email
                            $student = $modelUser->findByEmail($sortedData[$i]['Email(Marker Student)']);

                            $modelMarkerStudentInfo = new MarkerStudentInfo();
                            $modelMarkerStudentInfo->individual_assessment_id = $temp_individual_assessment_id;
                            $modelMarkerStudentInfo->completed = 0;

                            // Student not exists, regist new student.
                            if (empty($student)) {
                                
                                $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                                $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                                $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                                $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                                $modelUser->type = '1';
                                $modelUser->setPassword(self::DEFAULTPASS);
                                $modelUser->generateAuthKey();

                                if($flag = $modelUser->save(false)) {
                                    $modelMarkerStudentInfo->marker_student_id = $modelUser->id;
                                }
                            } else {
                                $modelMarkerStudentInfo->marker_student_id = $student->id;
                            }

                            if ($flag = $modelMarkerStudentInfo->save(false)) {
                            } else {
                                break;
                            }
                        }
                    }
                }

                $i++;
            } while ($i < count($sortedData));
        }
        return $flag;
    }

    public function registAssessmentInfo($model, $modelsSection, $modelsItem, $modelsRubric)
    {
        $flag = true;

        foreach ($modelsSection as $indexSection => $modelSection) {

            if ($flag === false) {
                break;
            }

            $modelSection->assessment_id = $model->id;

            if ($flag = $modelSection->save(false)) {
            } else {
                break;
            }

            if (isset($modelsItem[$indexSection]) && is_array($modelsItem[$indexSection])) {
                foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) {

                    $modelItem->section_id = $modelSection->id;

                    if ($flag = $modelItem->save(false)) {
                    } else {
                        break;
                    }

                    if (isset($modelsRubric[$indexSection][$indexItem]) && is_array($modelsRubric[$indexSection][$indexItem])) {
                        foreach ($modelsRubric[$indexSection][$indexItem] as $indexRubric => $modelRubric) {

                            if (!empty($modelRubric->level) && !empty($modelRubric->weight) && !empty($modelRubric->description)) {
                                $modelRubric->item_id = $modelItem->id;

                                if ($flag = $modelRubric->save(false)) {
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($flag) {

            $modelLecturerAssessment = new LecturerAssessment();

            $modelLecturerAssessment->assessment_id = $model->id;
            $modelLecturerAssessment->lecturer_id = Yii::$app->user->id;

            if ($flag = $modelLecturerAssessment->save(false)) {

                $coordinatorList = Yii::$app->request->post('selection');

                if (!empty($coordinatorList)) {
                    foreach ($coordinatorList as $coorinator) {

                        $modelLecturerAssessment = new LecturerAssessment();
                        $modelLecturerAssessment->assessment_id = $model->id;
                        $modelLecturerAssessment->lecturer_id = $coorinator;

                        if ($flag = $modelLecturerAssessment->save(false)) {
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }
}

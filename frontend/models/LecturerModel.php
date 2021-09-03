<?php

namespace frontend\models;

use common\models\Assessments;
use common\models\GroupAssessment;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessment;
use common\models\LecturerAssessment;
use common\models\MarkerStudentInfo;
use common\models\User;
use moonland\phpexcel\Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * ContactForm is the model behind the contact form.
 */
class LecturerModel extends Model
{
    const DEFAULTPASS = "00000000";
    const UNMARK = 0;
    const MARKED = 1;

    const LECTURER = 0;
    const STUDENT = 1;

    const INACTIVE = 0;
    const ACTIVE = 1;

    const INCOMPLETE = 0;
    const COMPLETE = 1;

    const G_PEER_REVIEW = 0;
    const G_PEER_REVIEW_MARK = 1;
    const G_SELF_ASSESS_PEER_REVIEW = 2;
    const SELF_ASSESSMENT = 3;
    const PEER_MARKING = 4;
    
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
            ->select(["CONCAT(first_name, ' ', last_name) as name, user.email as email"])
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
     * Get coordinators.
     *
     * @param int assessment_id
     * @return coorinators
     */
    public function getMaxGroupNumber($id)
    {
        $max_group_number = (new yii\db\Query())
            ->select(["Max(group_number) as max_group_number"])
            ->from('group_assessment')
            ->join('INNER JOIN', 'assessments', 'group_assessment.assessment_id = assessments.id')
            ->where('assessments.id = :id')
            ->addParams([
                ':id' => $id
                ])
            ->one();
        return $max_group_number['max_group_number'] + 1;
    }

    /**
     * Get coordinators.
     *
     * @param int assessment_id
     * @return boolean
     */
    public function registGroupInfo($group, $groupStudents)
    {
        $flag = true;
        if ($group->save(false)) {

            $group_id = $group->id;

            foreach ($groupStudents as $groupStudent) {
                $modelUser = new User();

                //Get student info by email
                $student = $modelUser->findByEmail($groupStudent->email);

                $modelGroupStudentInfo = new GroupStudentInfo();
                $modelGroupStudentInfo->group_id = $group_id;
                $modelGroupStudentInfo->marked = 0;
                $modelGroupStudentInfo->completed = 0;

                // If student not exist, regist
                if (empty($student)) {

                    $modelUser->first_name = $groupStudent->first_name;
                    $modelUser->last_name = $groupStudent->last_name;
                    $modelUser->matric_number = $groupStudent->matric_number;
                    $modelUser->email = $groupStudent->email;
                    $modelUser->type = self::STUDENT;
                    $modelUser->setPassword(self::DEFAULTPASS);
                    $modelUser->generateAuthKey();

                    if($modelUser->save(false)) {
                        $modelGroupStudentInfo->student_id = $modelUser->id;
                    } else {
                        $flag = false;
                        break;
                    }
                } else {
                    $modelGroupStudentInfo->student_id = $student->id;
                }

                if ($modelGroupStudentInfo->save(false)) {
                } else {
                    $flag = false;
                    break;
                }
            }

        } else {
            $flag = false;
        }
        return $flag;
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
        $query->where(['status' => 10, 'type' => self::LECTURER]);
        $query->andWhere(['<>','id', Yii::$app->user->id]);
        $query->all();

        $coorinators = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
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
                    ga.marked"])
            ->from('group_assessment as ga')
            ->join('INNER JOIN', 'group_student_info as gsi', 'ga.id = gsi.group_id')
            ->where('ga.assessment_id = :assessment_id')
            ->addParams([
                ':assessment_id' => $id, 
                ])
            ->groupBy('ga.id', 'ga.marked')
            ->all();

        $inconsistent = [];
        $completed = [];
        $incomplete = [];

        if(!empty($groupsInfo)) {

            foreach ($groupsInfo as $group) {
                if ($group["toalCount"] > 0 && $group["completeCount"] == 0) {
                    array_push($incomplete, $group);		
                } else if ($group["toalCount"] == $group["completeCount"]) {
                    array_push($completed, $group);
                } else {
                    array_push($inconsistent, $group);
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
    public function getStudentMarkStatus($id, $assessment_type)
    {

        $individualsInfo = (new yii\db\Query())
            ->select(["COUNT(*) as toalCount,
                    Sum(msi.completed) as completeCount,
                    ia.id,
                    CONCAT(user.first_name, ' ', user.last_name) as student_name,
                    ia.marked"])
            ->from('individual_assessment as ia')
            ->join('INNER JOIN', 'marker_student_info as msi', 'ia.id = msi.individual_assessment_id')
            ->join('LEFT OUTER JOIN', 'user', 'ia.student_id = user.id')
            ->where('ia.assessment_id = :assessment_id')
            ->addParams([
                ':assessment_id' => $id, 
                ])
            ->groupBy('ia.id', 'ia.marked')
            ->all();

        if ($assessment_type == 3) {
            $individualInfo = $individualsInfo;
        } else if ($assessment_type == 4) {
            $inconsistent = [];
            $completed = [];
            $incomplete = [];
    
            if(!empty($individualsInfo)) {
    
                foreach ($individualsInfo as $individual) {
                    if ($individual["toalCount"] > 0 && $individual["completeCount"] == 0) {
                        array_push($incomplete, $individual);		
                    }
                    else if ($individual["toalCount"] == $individual["completeCount"]) {
                        array_push($completed, $individual);
                    } else {
                        array_push($inconsistent, $individual);
                    }
                }
            }

            $individualInfo = [
                'inconsistent' => [],
                'completed' => [],
                'incomplete' => [],
            ];
            ArrayHelper::setValue($individualInfo, 'inconsistent', $inconsistent);
            ArrayHelper::setValue($individualInfo, 'completed', $completed);
            ArrayHelper::setValue($individualInfo, 'incomplete', $incomplete);
        }

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

    public function getStudentId($firstName, $lastName, $matricNumber, $email)
    {
        $studentId = '';
        $modelUser = new User();

        //Get student info by email
        $student = $modelUser->findByEmail($email);

        // If student not exist, regist
        if (empty($student)) {

            $modelUser->first_name = $firstName;
            $modelUser->last_name = $lastName;
            $modelUser->matric_number = $matricNumber;
            $modelUser->email = $email;
            $modelUser->type = self::STUDENT;
            $modelUser->setPassword(self::DEFAULTPASS);
            $modelUser->generateAuthKey();

            if($modelUser->save(false)) {
                $studentId = $modelUser->id;
            }
        } else {
            $studentId = $student->id;
        }

        return $studentId;
    }

    public function registGroupStudentInfo($firstName, $lastName, $matricNumber, $email, $groupId)
    {
        $flag = true;

        $studentId = $this->getStudentId($firstName, $lastName, $matricNumber, $email);

        if (!empty($studentId)) {
            $modelGroupStudentInfo = new GroupStudentInfo();
            $modelGroupStudentInfo->group_id = $groupId;
            $modelGroupStudentInfo->marked = self::UNMARK;
            $modelGroupStudentInfo->completed = self::INCOMPLETE;
            $modelGroupStudentInfo->student_id = $studentId;

            if ($modelGroupStudentInfo->save(false)) {
            } else {
                $flag = false;
            }
        } else {
            $flag = false;
        }

        return $flag;
    }

    public function registDatafromUpload($excelData, $model)
    {
        $flag = true;

        $i = 0;

        // For Group Assessment
        if ($model->assessment_type == self::G_PEER_REVIEW
            || $model->assessment_type == self::G_PEER_REVIEW_MARK
            || $model->assessment_type == self::G_SELF_ASSESS_PEER_REVIEW) {

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

                        $flag = $this->registGroupStudentInfo($sortedData[$i]['First Name'],
                            $sortedData[$i]['Last Name'],
                            $sortedData[$i]['Last Name'],
                            $sortedData[$i]['Email'],
                            $temp_Group_id);

                        if($flag) {
                        } else {
                            break;
                        }
                        
                    } else {
                        // Regist new Group Assessment
                        $temp_Group_name = $sortedData[$i]['Group Name'];;

                        $modelGroupAssessment = new GroupAssessment();

                        $modelGroupAssessment->name = $temp_Group_name;
                        $modelGroupAssessment->assessment_id = $model->id;
                        $modelGroupAssessment->group_number = $group_number;
                        $modelGroupAssessment->marked = self::UNMARK;

                        if ($flag = $modelGroupAssessment->save(false)) {

                            $temp_Group_id = $modelGroupAssessment->id;
                            $group_number++;

                            $flag = $this->registGroupStudentInfo($sortedData[$i]['First Name'],
                                $sortedData[$i]['Last Name'],
                                $sortedData[$i]['Last Name'],
                                $sortedData[$i]['Email'],
                                $temp_Group_id);

                            if($flag) {
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
        else if ($model->assessment_type == self::SELF_ASSESSMENT) {

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

                    $studentId = $this->getStudentId(
                                    $data['First Name'],
                                    $data['Last Name'],
                                    $data['Matriculation Number'],
                                    $data['Email']);

                    // If student not exist, regist
                    if (!empty($studentId)) {

                        $modelIndividualAssessment = new IndividualAssessment();
                        $modelIndividualAssessment->assessment_id = $model->id;
                        $modelIndividualAssessment->student_number = $student_number;
                        $modelIndividualAssessment->marked = self::UNMARK;
                        $modelIndividualAssessment->student_id = $studentId;

                        if ($modelIndividualAssessment->save(false)) {

                            $student_number++;
    
                            $modelMarkerStudentInfo = new MarkerStudentInfo();
                            $modelMarkerStudentInfo->individual_assessment_id = $modelIndividualAssessment->id;
                            $modelMarkerStudentInfo->completed = self::INCOMPLETE;
                            $modelMarkerStudentInfo->marker_student_id = $modelIndividualAssessment->student_id;
    
                            if ($modelMarkerStudentInfo->save(false)) {
                            } else {
                                $flag = false;
                                break;
                            }
                        } else {
                            $flag = false;
                            break;
                        }
                    }
                }
            }
        }

        else if ($model->assessment_type == self::PEER_MARKING) {

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
                        
                        $markerStudentId = $this->getStudentId(
                            $sortedData[$i]['First Name(Marker Student)'],
                            $sortedData[$i]['Last Name(Marker Student)'],
                            $sortedData[$i]['Matriculation Number(Marker Student)'],
                            $sortedData[$i]['Email(Marker Student)']);

                        if (!empty($studentId)) {
                            $modelMarkerStudentInfo = new MarkerStudentInfo();
                            $modelMarkerStudentInfo->individual_assessment_id = $temp_individual_assessment_id;
                            $modelMarkerStudentInfo->completed = self::INCOMPLETE;
                            $modelMarkerStudentInfo->marker_student_id = $markerStudentId;

                            if ($modelMarkerStudentInfo->save(false)) {
                            } else {
                                $flag = false;
                                break;
                            }
                        } else {
                            $flag = false;
                            break;
                        }

                    } else {
                        
                        $temp_student_email = $sortedData[$i]['Email'];

                        $studentId = $this->getStudentId(
                            $sortedData[$i]['First Name'],
                            $sortedData[$i]['Last Name'],
                            $sortedData[$i]['Matriculation Number'],
                            $sortedData[$i]['Email']);

                        // If student not exist, regist
                        if (!empty($studentId)) {

                            $modelIndividualAssessment = new IndividualAssessment();
                            $modelIndividualAssessment->file_path = $sortedData[$i]['Work File'];
                            $modelIndividualAssessment->assessment_id = $model->id;
                            $modelIndividualAssessment->marked = self::UNMARK;
                            $modelIndividualAssessment->student_id = $studentId;

                            if ($modelIndividualAssessment->save(false)) {

                                $temp_individual_assessment_id = $modelIndividualAssessment->id;
                                $student_number++;

                                $markerStudentId = $this->getStudentId(
                                    $sortedData[$i]['First Name(Marker Student)'],
                                    $sortedData[$i]['Last Name(Marker Student)'],
                                    $sortedData[$i]['Matriculation Number(Marker Student)'],
                                    $sortedData[$i]['Email(Marker Student)']);

                                if (!empty($studentId)) {
                                    $modelMarkerStudentInfo = new MarkerStudentInfo();
                                    $modelMarkerStudentInfo->individual_assessment_id = $temp_individual_assessment_id;
                                    $modelMarkerStudentInfo->completed = self::INCOMPLETE;
                                    $modelMarkerStudentInfo->marker_student_id = $markerStudentId;

                                    if ($modelMarkerStudentInfo->save(false)) {
                                    } else {
                                        $flag = false;
                                        break;
                                    }
                                } else {
                                    $flag = false;
                                    break;
                                }

                            } else {
                                $flag = false;
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

            if ($modelSection->save(false)) {
            } else {
                $flag = false;
                break;
            }

            if (isset($modelsItem[$indexSection]) && is_array($modelsItem[$indexSection])) {
                foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) {

                    $modelItem->section_id = $modelSection->id;

                    if ($modelItem->save(false)) {
                    } else {
                        $flag = false;
                        break;
                    }

                    if (isset($modelsRubric[$indexSection][$indexItem]) && is_array($modelsRubric[$indexSection][$indexItem])) {
                        foreach ($modelsRubric[$indexSection][$indexItem] as $indexRubric => $modelRubric) {

                            if (!empty($modelRubric->level) && !empty($modelRubric->weight) && !empty($modelRubric->description)) {
                                $modelRubric->item_id = $modelItem->id;

                                if ($modelRubric->save(false)) {
                                } else {
                                    $flag = false;
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

                        if ($modelLecturerAssessment->save(false)) {
                        } else {
                            $flag = false;
                            break;
                        }
                    }
                }
            }
        }
        return $flag;
    }

    /**
     * Get brief result.
     *
     * @param int assessment_id
     * @return coorinators
     */
    public function getBriefResult($id, $assessment_type)
    {

        if ($assessment_type == self::PEER_MARKING
            || $assessment_type == self::SELF_ASSESSMENT) {

            $data = (new yii\db\Query())
                ->select('user.first_name as first_name,
                    user.last_name as last_name,
                    user.email as email,
                    grade.grade as mark')
                ->from('individual_assessment as ia')
                ->join('INNER JOIN', 'assessments', 'ia.assessment_id = assessments.id')
                ->join('LEFT OUTER JOIN', 'user', 'ia.student_id = user.id')
                ->join('LEFT OUTER JOIN', 'grade', 'ia.mark_value >= grade.min_mark and ia.mark_value < grade.max_mark')
                ->where('assessments.id = :id')
                ->addParams([
                    ':id' => $id
                    ])
                ->all();

        } else if ($assessment_type == self::G_SELF_ASSESS_PEER_REVIEW
            || $assessment_type == self::G_PEER_REVIEW
            || $assessment_type == self::G_PEER_REVIEW_MARK) {

            $data = (new yii\db\Query())
                ->select('user.first_name as first_name,
                        user.last_name as last_name,
                        user.email as email,
                        ga.name as group_name,
                        g2.grade as group_mark,
                        g1.grade as individual_mark')
                ->from('group_student_info as gsi')
                ->join('INNER JOIN', 'group_assessment as ga', 'gsi.group_id = ga.id')
                ->join('INNER JOIN', 'assessments', 'ga.assessment_id = assessments.id')
                ->join('LEFT OUTER JOIN', 'user', 'gsi.student_id = user.id')
                ->join('LEFT OUTER JOIN', 'grade as g1', 'gsi.mark >= g1.min_mark and gsi.mark < g1.max_mark')
                ->join('LEFT OUTER JOIN', 'grade as g2', 'ga.mark >= g2.min_mark and ga.mark < g2.max_mark')
                ->where('assessments.id = :id')
                ->addParams([
                    ':id' => $id
                    ])
                ->all();
        }

        return $data;
    }

    /**
     * Export results to excel.
     *
     * @param int assessment_id
     * @return coorinators
     */
    public function getExportData($id)
    {
        $model = Assessments::findOne($id);
        $assessment_type = $model->assessment_type;
        $header = [];

        if ($assessment_type == self::G_PEER_REVIEW_MARK
            || $assessment_type == self::G_PEER_REVIEW
            || $assessment_type == self::G_SELF_ASSESS_PEER_REVIEW) {
            $data = $this->getBriefResult($id, $assessment_type);
            $header = ['first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'email' => 'Email',
                    'group_name' => 'Group Name',
                    'group_mark' => 'Group Mark',
                    'individual_mark' => 'Individual Mark',];
        } else if ($assessment_type == self::SELF_ASSESSMENT
            || $assessment_type == self::PEER_MARKING) {
            $data = $this->getBriefResult($id, $assessment_type);
            $header = ['first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'email' => 'Email',
                    'mark' => 'Mark',];
        }
        
        $filename = 'Assessment_'. $model->name;

        if(empty($data)) {
            echo json_encode(['error'=>'Problem with the exporting.']);
        } else {

            $dataKeys = array_keys($data[0]);

            Excel::widget([
                'models' => $data,
                'fileName' => $filename,
                'mode' => 'export',
                'asAttachment' => true,
                'columns' => $dataKeys,
                'headers' => $header, 
            ]);
        }
    }

    /**
     * Send reminder emails to student who doesn't finished their peer review.
     *
     * @param int assessment_id
     * @return coorinators
     */
    public function sendReminderEmail($id)
    {
        $model = Assessments::findOne($id);
        $assessment_type = $model->assessment_type;
        $incomplete = [];

        $flag = true;

        if ($assessment_type == self::G_PEER_REVIEW_MARK
            || $assessment_type == self::G_PEER_REVIEW
            || $assessment_type == self::G_SELF_ASSESS_PEER_REVIEW) {
            
            $incomplete = (new yii\db\Query())
                ->select('user.first_name as first_name,
                        user.last_name as last_name,
                        user.email as email')
                ->from('group_assessment as ga')
                ->join('INNER JOIN', 'group_student_info as gsi', 'ga.id = gsi.group_id')
                ->join('LEFT OUTER JOIN', 'user', 'gsi.student_id = user.id')
                ->where('ga.assessment_id = :assessment_id')
                ->andWhere('gsi.complete = 0')
                ->addParams([
                    ':assessment_id' => $id, 
                    ])
                ->all();

        } else if ($assessment_type == self::SELF_ASSESSMENT
            || $assessment_type == self::PEER_MARKING) {
            $incomplete = (new yii\db\Query())
                ->select('user.first_name as first_name,
                    user.last_name as last_name,
                    user.email as email')
                ->from('individual_assessment as ia')
                ->join('INNER JOIN', 'marker_student_info as msi', 'msi.individual_assessment_id = ia.id')
                ->join('LEFT OUTER JOIN', 'user', 'msi.marker_student_id = user.id')
                ->where('ia.assessment_id = :id')
                ->addParams([
                    ':id' => $id
                    ])
                ->all();
        }

        if (!empty($incomplete)) {
            
            foreach ($incomplete as $reciver) {
                Yii::$app->mailer->compose(
                    'email_reminder', [
                        'student_name' => $reciver['first_name'] . " " . $reciver['last_name'],
                        'assessment_name' => $model->name,
                        'deadline' => date("d.m.Y H:i",strtotime($model->deadline)),
                        'link' => Url::base(true),
                        'lecturer_name' => Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name
                    ]
                )
                ->setFrom(Yii::$app->user->identity->email)
                ->setTo($reciver['email'])
                ->setSubject($model->name . ' - Peer Assessment Reminder')
                ->send();
            }
        } else {
            $flag = false;
        }

        return $flag;
    }

    /**
     * Send result email to student.
     *
     * @param int assessment_id
     * @return coorinators
     */
    public function sendResult($id)
    {
        $model = Assessments::findOne($id);
        $assessment_type = $model->assessment_type;
        $data = [];
        $view = '';

        $flag = true;

        if ($assessment_type == self::G_PEER_REVIEW_MARK
            || $assessment_type == self::G_PEER_REVIEW
            || $assessment_type == self::G_SELF_ASSESS_PEER_REVIEW) {

            $count = (new yii\db\Query())
                ->select('count(*)')
                ->from('group_assessment as ga')
                ->where('ga.assessment_id = :assessment_id')
                ->andWhere('ga.marked = 0')
                ->addParams([
                    ':assessment_id' => $id, 
                    ])
                ->column();
                
            if($count[0] > 0) {
                Yii::$app->session->setFlash('error', 'Not all groups are marked.');
            } else {

                $data = $this->getBriefResult($id, $assessment_type);
                $view = 'email_results_group';
            }

        } else if ($assessment_type == self::SELF_ASSESSMENT
            || $assessment_type == self::PEER_MARKING) {
            $count = (new yii\db\Query())
                ->select('count(*)')
                ->from('individual_assessment as ia')
                ->where('ia.assessment_id = :id')
                ->andWhere('ia.marked = 0')
                ->addParams([
                    ':id' => $id
                    ])
                ->column();

            if($count[0] > 0) {
                Yii::$app->session->setFlash('error', 'Not all students are marked.');
            } else {

                $data = $this->getBriefResult($id, $assessment_type);
                $view = 'email_results_individual';
            }
        }        

        if (!empty($data)) {

            $coordinators = $this->getCoordinators($id);
            $cc = [];

            if(!empty($coordinators)) {
                foreach($coordinators as $coordinator) {
                    array_push($cc, $coordinator['email']);
                }
            }

            if ($assessment_type == self::G_PEER_REVIEW_MARK
                || $assessment_type == self::G_PEER_REVIEW
                || $assessment_type == self::G_SELF_ASSESS_PEER_REVIEW) {
                    foreach ($data as $reciver) {
                        Yii::$app->mailer->compose(
                                'email_results_group', [
                                'student_name' => $reciver['first_name'] . " " . $reciver['last_name'],
                                'assessment_name' => $model->name,
                                'group_mark' => $reciver['group_mark'],
                                'individual_mark' => $reciver['individual_mark'],
                                'link' => Url::base(true),
                                'lecturer_name' => Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name
                            ]
                        )
                        ->setFrom(Yii::$app->user->identity->email)
                        ->setTo($reciver['email'])
                        ->setCc($cc)
                        ->setSubject($model->name . ' - Marks and Feedback')
                        ->send();
                    }
            } else if ($assessment_type == self::SELF_ASSESSMENT
                || $assessment_type == self::PEER_MARKING) {
                    foreach ($data as $reciver) {
                        Yii::$app->mailer->compose(
                                'email_results_individual', [
                                'student_name' => $reciver['first_name'] . " " . $reciver['last_name'],
                                'assessment_name' => $model->name,
                                'mark' => $reciver['mark'],
                                'link' => Url::base(true),
                                'lecturer_name' => Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->last_name
                            ]
                        )
                        ->setFrom(Yii::$app->user->identity->email)
                        ->setTo($reciver['email'])
                        ->setCc($cc)
                        ->setSubject($model->name . ' - Marks and Feedback')
                        ->send();
                    }
            }
        } else {
            $flag = false;
        }

        return $flag;
    }
}

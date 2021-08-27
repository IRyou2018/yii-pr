<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupAssessmentDetail;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessmentDetail;
use common\models\Items;
use common\models\MarkerStudentInfo;
use common\models\Sections;
use Exception;
use frontend\models\ArrayValidator;
use frontend\models\GroupItemMark;
use frontend\models\StudentModel;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LecturerController
 */
class StudentController extends Controller
{
    const GROUP = 0;
    const INDIVIDUAL = 1;

    const UNCOMPLETE = 0;
    const COMPLETED = 1;

    const INDIVIDUAL_ITEM = 0;
    const GROUP_ITEM = 1;

    const G_PEER_REVIEW = 0;
    const G_PEER_ASSESSMENT = 1;
    const G_PEER_R_A = 2;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@']
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Displays a single Assessments model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDashboard()
    {
        $studentModel = new StudentModel();
        $unCompletedAssessment = $studentModel->searchAssessment(self::UNCOMPLETE);
        $completedAssessment = $studentModel->searchAssessment(self::COMPLETED);
        $feedbacks = $studentModel->searchAssessment(self::COMPLETED);

        return $this->render('dashboard', [
            'unCompletedAssessment' => $unCompletedAssessment,
            'completedAssessment' => $completedAssessment,
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Displays a single Assessments model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionArchived()
    {
        $studentModel = new StudentModel();
        $completedAssessment = $studentModel->searchArchivedAssessment(self::COMPLETED);
        $feedbacks = $studentModel->searchAssessment(self::COMPLETED);

        return $this->render('archived', [
            'completedAssessment' => $completedAssessment,
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Mark Individual Assessment
     * @return mixed
     */
    public function actionSubmitIndividual($id, $assessment_id)
    {
        $model = Assessments::findOne($assessment_id);

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsAssessmentDetail = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {
                $assessmentDetail = new IndividualAssessmentDetail();
                $assessmentDetail->item_id = $item->id;
                $assessmentDetail->marker_student_info_id = $id;

                $modelsAssessmentDetail[$indexSection][$index] = $assessmentDetail;
            }
        }
            
        if ($this->request->isPost) {
            
            if (isset($_POST['IndividualAssessmentDetail'][0][0])) {

                $index = 0;
                $reviewDetails = [];
                $valid = true;

                // Get Input value
                foreach ($_POST['IndividualAssessmentDetail'] as $indexSection => $individualAssessmentDetails) {
                    
                    foreach ($individualAssessmentDetails as $indexItem => $individualAssessmentDetail) {
                        
                        $data['IndividualAssessmentDetail'] = $individualAssessmentDetail;
                        $modelAssessmentDetail = new IndividualAssessmentDetail();
                        $modelAssessmentDetail->load($data);
                        $modelAssessmentDetail->scenario = 'submit';

                        $modelsAssessmentDetail[$indexSection][$indexItem] = $modelAssessmentDetail;

                        // Input validation
                        if($modelAssessmentDetail->validate()) {
                            $reviewDetails[$index] = $modelAssessmentDetail;
                        } else {
                            $valid = false;
                        }
                        $index++;
                    }
                }

                if($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();

                    try {

                        $flag = true;

                        foreach ($reviewDetails as $index => $reviewDetail) {

                            if ($flag = $reviewDetail->save(false)) {
                            } else {
                                break;
                            }
                        }
                        
                        if($flag) {
                            $markerInfo = MarkerStudentInfo::findOne($id);

                            $markerInfo->completed = self::COMPLETED;

                            $flag = $markerInfo->save(false);
                        }

                        if ($flag) {
                            $transaction->commit();
                            return $this->redirect(['dashboard']);
                        } else {

                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('submit-individual', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsAssessmentDetail' => (empty($modelsAssessmentDetail)) ? [[new IndividualAssessmentDetail()]] :  $modelsAssessmentDetail,
        ]);
    }

    /**
     * View Individual Assessment
     * @return mixed
     */
    public function actionViewIndividual($id, $assessment_id)
    {
        $model = Assessments::findOne($assessment_id);

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsAssessmentDetail = [];

        $individualMarkResults = MarkerStudentInfo::findOne($id)->individualAssessmentDetails;

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {

                foreach ($individualMarkResults as $individualMark) {

                    if ($item->id == $individualMark->item_id) {
                        $modelsAssessmentDetail[$indexSection][$index] = $individualMark;
                    }
                }
            }
        }
        
        return $this->render('view-individual', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsAssessmentDetail' => (empty($modelsAssessmentDetail)) ? [[[new IndividualAssessmentDetail()]]] :  $modelsAssessmentDetail,
        ]);
    }

    /**
     * Submit group reviews.
     * 
     * @return mixed
     */
    public function actionSubmitGroup($id, $assessment_id)
    {
        $model = Assessments::findOne($assessment_id);

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsGroupAssessmentDetail = [];
        $modelsGroupItemMark = [];

        $groupStudents = GroupStudentInfo::findOne($id)->group->groupStudentInfos;

        $countGroupMember = count($groupStudents);

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {

                if ($model->assessment_type == self::G_PEER_R_A) {
                    $modelGroupItemMark = new GroupItemMark();
                    $modelGroupItemMark->item_max_mark = $item->max_mark_value;
                    $modelsGroupItemMark[$indexSection][$index] = $modelGroupItemMark;
                }

                if ($item->item_type == self::INDIVIDUAL_ITEM) {
                    foreach($groupStudents as $indexStudent => $groupStudent) {
                        $modelGroupDetail = new GroupAssessmentDetail();
                        $modelGroupDetail->item_id = $item->id;
                        $modelGroupDetail->group_student_Info_id = $id;
                        $modelGroupDetail->work_student_id = $groupStudent->student_id;

                        if ($model->assessment_type == self::G_PEER_R_A || $model->assessment_type == self::G_PEER_REVIEW) {

                            $contribution = 0;
                            if($countGroupMember % 2 != 0) {
                                $contribution = 100-(round(100/$countGroupMember,0,PHP_ROUND_HALF_DOWN) * $countGroupMember - 1);
                            } else {
                                $contribution = round(100/$countGroupMember,0,PHP_ROUND_HALF_DOWN);
                            }

                            $modelGroupDetail->contribution = $contribution;
                        }
    
                        $modelsGroupAssessmentDetail[$indexSection][$index][$indexStudent] = $modelGroupDetail;
                    }
                } else if ($item->item_type == self::GROUP) {
                    $modelGroupDetail = new GroupAssessmentDetail();
                    $modelGroupDetail->item_id = $item->id;
                    $modelGroupDetail->group_student_Info_id = $id;
                    $modelsGroupAssessmentDetail[$indexSection][$index][0] = $modelGroupDetail;
                }
            }
        }
        
        if ($this->request->isPost) {
            
            if (isset($_POST['GroupItemMark'][0][0])) {
                foreach ($_POST['GroupItemMark'] as $indexSection => $items) {
                    foreach ($items as $indexItem => $item) {
                        $data['GroupItemMark'] = $item;
                        $modelGroupItemMark = new GroupItemMark();
                        $modelGroupItemMark->load($data);
                        $modelGroupItemMark->scenario = 'submit';

                        $modelsGroupItemMark[$indexSection][$indexItem] = $modelGroupItemMark;
                        if ($modelGroupItemMark->validate()) {
                        } else {
                            $valid = false;
                        }
                    }
                }
            }

            if (isset($_POST['GroupAssessmentDetail'][0][0][0])) {

                $index = 0;
                $groupDetails = [];
                $valid = true;

                // Get Input value
                foreach ($_POST['GroupAssessmentDetail'] as $indexSection => $groupDetailsSection) {

                    foreach ($groupDetailsSection as $indexItem => $groupDetailsItem) {
                    
                        foreach ($groupDetailsItem as $indexStudent => $groupDetailStudent) {
                            
                            $data['GroupAssessmentDetail'] = $groupDetailStudent;
                            $groupDetail = new GroupAssessmentDetail();
                            $groupDetail->load($data);
                            if ($model->assessment_type == self::G_PEER_ASSESSMENT) {
                                $groupDetail->scenario = 'submit';
                            } else if ($model->assessment_type == self::G_PEER_R_A) {
                                $groupDetail->mark = $modelsGroupItemMark[$indexSection][$indexItem]->mark;
                            }

                            $modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent] = $groupDetail;

                            // Input validation
                            if($groupDetail->validate()) {
                                $groupDetails[$index] = $groupDetail;
                            } else {
                                $valid = false;
                            }
                            $index++;
                        }
                    }
                }

                if ($model->assessment_type == self::G_PEER_REVIEW || $model->assessment_type == self::G_PEER_R_A) {
                    $arrayValidator = new ArrayValidator();
                    $valid = $arrayValidator->validateGroupDetail($modelsGroupAssessmentDetail, $countGroupMember);
                }

                if($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();

                    try {

                        $flag = true;

                        foreach ($groupDetails as $groupDetail) {

                            if ($flag = $groupDetail->save(false)) {
                            } else {
                                break;
                            }
                        }

                        if($flag) {
                            $modelGroupStudent = GroupStudentInfo::findOne($id);

                            $modelGroupStudent->completed = self::COMPLETED;

                            $flag = $modelGroupStudent->save(false);
                        }

                        if ($flag) {
                            $transaction->commit();
                            return $this->redirect(['dashboard']);
                        } else {

                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('submit-group', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsGroupItemMark' => (empty($modelsGroupItemMark)) ? [[new GroupItemMark()]] : $modelsGroupItemMark,
            'modelsGroupAssessmentDetail' => (empty($modelsGroupAssessmentDetail)) ? [[[new GroupAssessmentDetail()]]] :  $modelsGroupAssessmentDetail,
        ]);
    }

    /**
     * View completed group reviews.
     * 
     * @return mixed
     */
    public function actionViewGroup($id, $assessment_id)
    {
        $model = Assessments::findOne($assessment_id);

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsGroupAssessmentDetail = [];
        $marklist = [];

        $groupAssessmentDetails = GroupStudentInfo::findOne($id)->groupAssessmentDetails;

        $groupStudents = GroupStudentInfo::findOne($id)->group->groupStudentInfos;

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {

                if ($item->item_type == self::INDIVIDUAL_ITEM) {
                    
                    foreach($groupStudents as $indexStudent => $groupStudent) {

                        foreach($groupAssessmentDetails as $groupAssessmentDetail) {

                            if($groupStudent->student_id == $groupAssessmentDetail->work_student_id
                                && $item->id == $groupAssessmentDetail->item_id) {

                                $modelsGroupAssessmentDetail[$indexSection][$index][$indexStudent] = $groupAssessmentDetail;

                            }
                        }
                    }
                } else if ($item->item_type == self::GROUP) {

                    foreach($groupAssessmentDetails as $groupAssessmentDetail) {

                        if($item->id = $groupAssessmentDetail->item_id) {

                            $modelsGroupAssessmentDetail[$indexSection][$index][0] = $groupAssessmentDetail;
                        }
                    }
                }

                if ($model->assessment_type == self::G_PEER_R_A) {
                    $marklist[$indexSection][$index] = $modelsGroupAssessmentDetail[$indexSection][$index][0]->mark;
                }
            }
        }

        return $this->render('view-group', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsGroupAssessmentDetail' => (empty($modelsGroupAssessmentDetail)) ? [[[new GroupAssessmentDetail()]]] :  $modelsGroupAssessmentDetail,
            'marklist' => (empty($marklist)) ? [['']] :  $marklist,
        ]);
    }

}

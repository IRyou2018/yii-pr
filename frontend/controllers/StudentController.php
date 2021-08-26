<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupAssessment;
use common\models\GroupAssessmentDetail;
use common\models\GroupInfo;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessmentDetail;
use common\models\Items;
use common\models\MarkerStudentInfo;
use common\models\Rubrics;
use common\models\Sections;
use Exception;
use frontend\models\AssessmentsSearch;
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
        $searchModel = new AssessmentsSearch();
        $feedbacks = $searchModel->searchFeedbacks();
        $studentModel = new StudentModel();
        $unCompletedAssessment = $studentModel->searchAssessment(self::UNCOMPLETE);
        $completedAssessment = $studentModel->searchAssessment(self::COMPLETED);

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
        $searchModel = new AssessmentsSearch();
        $dataProvider = $searchModel->searchByLecturerID($this->request->queryParams);

        return $this->render('archived', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Mark Individual Assessment
     * @return mixed
     */
    public function actionSubmitIndividual($id, $assessment_id)
    {
        $model = $this->findModel($assessment_id);

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
        $model = $this->findModel($assessment_id);

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
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSubmitGroup($id, $assessment_id)
    {
        $model = $this->findModel($assessment_id);

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsGroupAssessmentDetail = [];
        $countGroupMember = 0;

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            $group_id = GroupStudentInfo::findOne($id)->group_id;

            $groupStudents = GroupAssessment::findOne($group_id)->groupStudentInfos;

            $countGroupMember = count($groupStudents);

            foreach ($items as $index => $item) {

                if ($item->item_type == self::INDIVIDUAL_ITEM) {
                    foreach($groupStudents as $indexStudent => $groupStudent) {
                        $modelGroupDetail = new GroupAssessmentDetail();
                        $modelGroupDetail->item_id = $item->id;
                        $modelGroupDetail->group_student_Info_id = $id;
                        $modelGroupDetail->work_student_id = $groupStudent->student_id;
    
                        $modelsGroupAssessmentDetail[$indexSection][$index][$indexStudent] = $modelGroupDetail;
                    }
                }
            }
        }
        
        // echo "<pre>";
        // print_r($modelsGroupAssessmentDetail);
        // print_r(count($groupStudents));
        // echo "</pre>";
        // exit;
        if ($this->request->isPost) {
            
            if (isset($_POST['GroupAssessmentDetail'][0][0][0])) {

                $index = 0;
                $groupDetails = [];
                $valid = true;

                // Get Input value
                foreach ($_POST['GroupAssessmentDetail'] as $indexSection => $groupDetailsSection) {
                    
                    foreach ($groupDetailsSection as $indexItem => $groupDetailsItem) {
                    
                        foreach ($groupDetailsItem as $index => $groupDetailStudent) {
                            
                            $data['GroupAssessmentDetail'] = $groupDetailStudent;
                            $groupDetail = new GroupAssessmentDetail();
                            $groupDetail->load($data);
                            $groupDetail->scenario = 'submit';

                            $modelsGroupAssessmentDetail[$indexSection][$indexItem] = $groupDetail;

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
            'countGroupMember' => $countGroupMember,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsGroupAssessmentDetail' => (empty($modelsGroupAssessmentDetail)) ? [[[new GroupAssessmentDetail()]]] :  $modelsGroupAssessmentDetail,
        ]);
    }

    /**
     * Finds the Assessments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Assessments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Assessments::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

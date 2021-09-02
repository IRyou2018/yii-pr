<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupAssessment;
use common\models\GroupAssessmentDetail;
use common\models\GroupAssessmentFeedback;
use common\models\GroupAssessmentGrade;
use common\models\IndividualAssessment;
use common\models\IndividualAssessmentDetail;
use common\models\IndividualAssessmentFeedback;
use common\models\Items;
use common\models\Rubrics;
use common\models\Sections;
use Exception;
use frontend\models\ArrayValidator;
use frontend\models\AssessmentsSearch;

use frontend\models\GroupStudent;
use frontend\models\LecturerModel;
use frontend\models\Model;
use frontend\models\Upload;
use moonland\phpexcel\Excel;
use tidy;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * LecturerController
 */
class LecturerController extends Controller
{
    const DEFAULTPASS = "00000000";
    const INACTIVE = 0;
    const ACTIVE = 1;
    const STUDENT = 1;

    const MARKED = 1;

    const UNCOMPLETE = 0;
    const COMPLETED = 1;

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
     * Displays current year Assessments.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDashboard()
    {
        $this->layout = 'lecturer';
        $searchModel = new AssessmentsSearch();
        $dataProvider = $searchModel->getCurrentYearAssessment();

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays archived Assessments.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionArchived()
    {
        $this->layout = 'lecturer';
        $searchModel = new AssessmentsSearch();
        $dataProvider = $searchModel->getArchivedAssessment();

        return $this->render('archived', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Add Group to Assessments.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAddGroup($id)
    {
        $model = Assessments::findOne($id);
        $group = new GroupAssessment();
        $group->assessment_id = $id;
        $groupStudents = [new GroupStudent()];

        
        if ($this->request->isPost) {
            if ($group->load($this->request->post())) {

                $groupStudents = Model::createMultiple(GroupStudent::classname());
                Model::loadMultiple($groupStudents, Yii::$app->request->post());
                
                $modelLecturer = new LecturerModel();
                $group_number = $modelLecturer->getMaxGroupNumber($id);
                $group->group_number = $group_number;
                $group->marked = 0;

                $valid = $group->validate();
                
                $valid = Model::validateMultiple($groupStudents) && $valid;
                
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {

                        $flag = $modelLecturer->registGroupInfo($group, $groupStudents);

                        if ($flag) {
                            $transaction->commit();

                            return $this->redirect(['assessment', 'id' => $id]);
                        } else {

                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
        }

        return $this->renderAjax('add-group', [
            'model' => $model,
            'group' => $group,
            'groupStudents' => $groupStudents,
        ]);
    }


    public function actionUpdateActive(){
        $status = Yii::$app->request->post('status');
        $id = Yii::$app->request->post('id');

        $model = $this->findModel($id);

        if($status == "true") {
            $model->active = self::ACTIVE;
            $message = 'Assessment: ' . $model->name . ' status has been set to Visible.';
        } else {
            $model->active = self::INACTIVE;
            $message = 'Assessment: ' . $model->name . ' status has been set to Invisible.';
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', $message);
        } else {
            Yii::$app->session->setFlash('error', 'Assessment: ' . $model->name . 'status update was failed.');
        }

        return $this->redirect('dashboard');
     }

    /**
     * Creates a new Assessment.
     * 
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Assessments();
        $modelsSection = [new Sections()];
        $modelsItem = [[new Items()]];
        $modelsRubric = [[[new Rubrics()]]];
        $modelUpload = new Upload();
        $modelLecturer = new LecturerModel();
        $coordinators = $modelLecturer->getCoordinatorList();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $modelUpload->load($this->request->post())) {
                
                $modelsSection = Model::createMultiple(Sections::classname());
                Model::loadMultiple($modelsSection, Yii::$app->request->post());

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelsSection) && $valid;

                if (isset($_POST['Items'][0][0])) {
                    foreach ($_POST['Items'] as $indexSection => $items) {
                        foreach ($items as $indexItem => $item) {
                            $data['Items'] = $item;
                            $modelItem = new Items();
                            $modelItem->load($data);
                            $modelsItem[$indexSection][$indexItem] = $modelItem;
                            if ($modelItem->validate()) {
                            } else {
                                $valid = false;
                            }
                        }
                    }
                }

                if (isset($_POST['Rubrics'][0][0][0])) {
                    foreach ($_POST['Rubrics'] as $indexSection => $items) {
                        foreach ($items as $indexItem => $rubrics) {
                            foreach ($rubrics as $indexRubric => $rubric) {
                                $data['Rubrics'] = $rubric;
                                $modelRubric = new Rubrics();
                                $modelRubric->load($data);
                                $modelsRubric[$indexSection][$indexItem][$indexRubric] = $modelRubric;
                                if ($modelRubric->validate()) {
                                } else {
                                    $valid = false;
                                }
                            }
                        }
                    }
                }

                if ($valid) {

                    $arrayValidator = new ArrayValidator();

                    if($arrayValidator->validateCreateAssessment($modelsSection, $modelsItem)) {
                    } else {
                        $valid = false;
                    }
                }

                // Get upload file name
                $modelUpload->file = UploadedFile::getInstance($modelUpload, 'file');
                // Set upload path
                $path = "uploads/";

                // Validate file extension
                if($modelUpload->validate()){
                } else {
                    $valid = false;
                }

                if ($valid) {
                    // Upload file to server
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $modelUpload->file->saveAs($path . time() . '.' . $modelUpload->file->extension);

                    $fileName = $path . time() . '.' . $modelUpload->file->extension;

                    $excelData = Excel::widget([
                        'mode' => 'import',
                        'fileName' => $fileName,
                        'setFirstRecordAsKeys' => true,
                        'setIndexSheetByName' => true,
                    ]);


                    // Validate file format
                    if ($modelUpload->validateTemplateFormat($excelData, $model->assessment_type)) {
                    } else {
                        $valid = false;
                    }

                    // Validate file Content
                    if ($modelUpload->validateInputContents($excelData, $model->assessment_type)) {
                    } else {
                        $valid = false;
                    }

                }

                if ($valid) {
                    $modelLecturer = new LecturerModel();
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {

                            if ($flag && count($excelData) > 0) {

                                
                                $flag = $modelLecturer->registDatafromUpload($excelData, $model);
                            }

                            if($flag) {
                                $flag = $modelLecturer->registAssessmentInfo($model, $modelsSection, $modelsItem, $modelsRubric);
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            Yii::$app->session->setFlash('success', 'Assessment has been successfully created.');
                            return $this->redirect(['dashboard']);
                        } else {
                            Yii::$app->session->setFlash('error', 'Create assessment failed. Please check your input.');
                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', 'Create assessment failed. Please check your input.');
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Input error occurs. Please check your input.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'modelUpload' => $modelUpload,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsRubric' => (empty($modelsRubric)) ? [[[new Rubrics()]]] : $modelsRubric,
            'coordinators' => $coordinators,
        ]);
    }

    /**
     * Updates an Assessment details.
     * 
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Assessment has been successfully updated.');
            return $this->redirect(['assessment', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an Assessment details.
     * 
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCopyCreate($id)
    {
        $copyModel = $this->findModel($id);

        $model = new Assessments();
        $modelsSection = $copyModel->sections;
        $modelsItem = [[new Items()]];
        $modelsRubric = [[[new Rubrics()]]];     
        $modelUpload = new Upload();
        $modelLecturer = new LecturerModel();
        $coordinators = $modelLecturer->getCoordinatorList();

        foreach ($modelsSection as $indexSection => $section) {
            $items = $section->items;

            foreach ($items as $indexItem => $item) {
                $rubrics = $item->rubrics;

                if(empty($rubrics[0]->id)) {
                    $modelsRubric[$indexSection][$indexItem][0] = new Rubrics();
                } else {
                    $modelsRubric[$indexSection][$indexItem] = $rubrics;
                }
            }

            $modelsItem[$indexSection] = $items;
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $modelUpload->load($this->request->post())) {
                
                $modelsSection = Model::createMultiple(Sections::classname());
                Model::loadMultiple($modelsSection, Yii::$app->request->post());

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelsSection) && $valid;

                if (isset($_POST['Items'][0][0])) {
                    foreach ($_POST['Items'] as $indexSection => $items) {
                        foreach ($items as $indexItem => $item) {
                            $data['Items'] = $item;
                            $modelItem = new Items();
                            $modelItem->load($data);
                            $modelsItem[$indexSection][$indexItem] = $modelItem;
                            if ($modelItem->validate()) {
                            } else {
                                $valid = false;
                            }
                        }
                    }
                }

                if (isset($_POST['Rubrics'][0][0][0])) {
                    foreach ($_POST['Rubrics'] as $indexSection => $items) {
                        foreach ($items as $indexItem => $rubrics) {
                            foreach ($rubrics as $indexRubric => $rubric) {
                                $data['Rubrics'] = $rubric;
                                $modelRubric = new Rubrics();
                                $modelRubric->load($data);
                                $modelsRubric[$indexSection][$indexItem][$indexRubric] = $modelRubric;
                                if ($modelRubric->validate()) {
                                } else {
                                    $valid = false;
                                }
                            }
                        }
                    }
                }

                if ($valid) {

                    $arrayValidator = new ArrayValidator();

                    if($arrayValidator->validateCreateAssessment($modelsSection, $modelsItem)) {
                    } else {
                        $valid = false;
                    }
                }

                // Get upload file name
                $modelUpload->file = UploadedFile::getInstance($modelUpload, 'file');
                // Set upload path
                $path = "uploads/";

                // Validate file extension
                if($modelUpload->validate()){
                } else {
                    $valid = false;
                }

                if ($valid) {
                    // Upload file to server
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $modelUpload->file->saveAs($path . time() . '.' . $modelUpload->file->extension);

                    $fileName = $path . time() . '.' . $modelUpload->file->extension;

                    $excelData = Excel::widget([
                        'mode' => 'import',
                        'fileName' => $fileName,
                        'setFirstRecordAsKeys' => true,
                        'setIndexSheetByName' => true,
                    ]);


                    // Validate file format
                    if ($modelUpload->validateTemplateFormat($excelData, $model->assessment_type)) {
                    } else {
                        $valid = false;
                    }

                    // Validate file Content
                    if ($modelUpload->validateInputContents($excelData, $model->assessment_type)) {
                    } else {
                        $valid = false;
                    }

                }

                if ($valid) {
                    $modelLecturer = new LecturerModel();
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {

                            if ($flag && count($excelData) > 0) {

                                
                                $flag = $modelLecturer->registDatafromUpload($excelData, $model);
                            }

                            if($flag) {
                                $flag = $modelLecturer->registAssessmentInfo($model, $modelsSection, $modelsItem, $modelsRubric);
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            Yii::$app->session->setFlash('success', 'Assessment has been successfully created.');
                            return $this->redirect(['dashboard']);
                        } else {
                            Yii::$app->session->setFlash('error', 'Create assessment failed. Please check your input.');
                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', 'Create assessment failed. Please check your input.');
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Input error occurs. Please check your input.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('copy-create', [
            'model' => $model,
            'modelUpload' => $modelUpload,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsRubric' => (empty($modelsRubric)) ? [[[new Rubrics()]]] : $modelsRubric,
            'coordinators' => $coordinators,
        ]);
    }

    /**
     * Mark invividual assessment.
     * 
     * @return mixed
     */
    public function actionMarkIndividual($id)
    {
        $individualAssessmentInfo = IndividualAssessment::findOne($id);
        $workStudentName = $individualAssessmentInfo->studentName;
        $model = $individualAssessmentInfo->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $makerInfos = $individualAssessmentInfo->markerStudentInfos;
        $individualDetails = [];
        foreach ($makerInfos as $index => $makerInfo) {
            $reviewDetails = $makerInfo->individualAssessmentDetails;

            $individualDetails[$index] = $reviewDetails;
        }

        $modelsIndividualFeedback = [];
        $supposedMarkList = [];
        $markerCommentsList = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $indexItem => $item) {
            
                if ($modelSection->section_type == 0) {

                    $supposedMark = null;
                    $tempComment = '';

                    if (!empty($individualDetails)) {

                        $totalProposedMark = 0;
                        $count = 0;

                        foreach ($individualDetails as $indexStudent => $reviewDetails) {

                            if (!empty($reviewDetails)) {

                                foreach($reviewDetails as $reviewDetail) {

                                    if ($reviewDetail->item_id == $item->id) {

                                        $totalProposedMark += $reviewDetail->mark;
                                        $count++;
                                        $tempComment = $tempComment . $reviewDetail->comment . " ";
                                        $modelsReviewDetail[$indexSection][$indexItem][$indexStudent] = $reviewDetail;
                                        break;
                                    }
                                }

                            } else {

                                foreach ($makerInfos as $indexStudent => $makerInfo) {
                                    $reviewDetail = new IndividualAssessmentDetail();
                                    $reviewDetail->marker_student_info_id = $makerInfo->id;
                                    $modelsReviewDetail[$indexSection][$indexItem][$indexStudent] = $reviewDetail;
                                }
                            }
                        }

                        if ($count > 0) {
                            $supposedMark = round($totalProposedMark/$count,0,PHP_ROUND_HALF_DOWN);
                        }
                    }

                    $supposedMarkList[$indexSection][$indexItem] = $supposedMark;
                    $markerCommentsList[$indexSection][$indexItem] = $tempComment;
                }

                $individualFeedback = new IndividualAssessmentFeedback();
                $individualFeedback->item_id = $item->id;
                $individualFeedback->individual_assessment_id = $id;
                $modelsIndividualFeedback[$indexSection][$indexItem] = $individualFeedback;

            }
        }

        if ($this->request->isPost) {
                
            if (isset($_POST['IndividualAssessmentFeedback'][0][0])) {

                $index = 0;
                $totalMark = 0;
                $student_id = $individualAssessmentInfo->student_id;
                $valid = true;

                // echo "<pre>";
                // print_r($_POST['IndividualAssessmentFeedback']);
                // echo "</pre>";
                // exit;

                foreach ($_POST['IndividualAssessmentFeedback'] as $indexSection => $feedbacks) {
                
                    foreach ($feedbacks as $indexItem => $feedback) {
                        
                        $data['IndividualAssessmentFeedback'] = $feedback;
                        $modelIndividualFeedback = new IndividualAssessmentFeedback();
                        $modelIndividualFeedback->load($data);
                        $modelIndividualFeedback->student_id = $student_id;
                        $modelIndividualFeedback->scenario = 'submit';

                        $modelsIndividualFeedback[$indexSection][$indexItem] = $modelIndividualFeedback;
                        
                        // Input validation
                        if($modelIndividualFeedback->validate()) {
                        } else {
                            $valid = false;
                        }

                        $index++;
                    }
                }
                
                $markValidate = new ArrayValidator();

                if ($markValidate->validateInputMarks($modelsIndividualFeedback, $supposedMarkList, $markerCommentsList)) {
                } else {
                    $valid = false;
                }

                if($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();

                    try {

                        $flag = true;
                        
                        foreach ($modelsIndividualFeedback as $indexSection => $feedbacks) {
                
                            foreach ($feedbacks as $indexItem => $feedback) {

                                if ($modelSection->section_type == 0) {
                                    if (empty($feedback->mark)) {
                                        $feedback->mark = $supposedMarkList[$indexSection][$indexItem];
                                    }

                                    if (empty($feedback->comment)) {
                                        $feedback->comment = $markerCommentsList[$indexSection][$indexItem];
                                    }
                                }
                                
                                $totalMark += $feedback->mark;
                                
                                if ($feedback->save(false)) {
                                } else {
                                    $flag = false;
                                    break;
                                }
                            }
                        }
                        
                        if($flag) {
                            $individualAssessment = $makerInfo->individualAssessment;

                            $individualAssessment->marked = self::COMPLETED;
                            $individualAssessment->mark_value = $totalMark;

                            $flag = $individualAssessment->save(false);
                        }

                        if ($flag) {
                            $transaction->commit();
                            Yii::$app->session->setFlash('success', 'Assessment mark has been successfully updated.');
                            return $this->redirect(['assessment', 'id' => $model->id]);
                        } else {
                            Yii::$app->session->setFlash('error', 'Assessment mark update failed. Please check your input.');
                            $transaction->rollBack();
                        }
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', 'Assessment mark update failed. Please check your input.');
                        $transaction->rollBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Input error occurs. Please check your input.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('mark-individual', [
            'model' => $model,
            'workStudentName' => $workStudentName,
            'supposedMarkList' => $supposedMarkList,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[[new IndividualAssessmentDetail()]]] :  $modelsReviewDetail,
            'modelsIndividualFeedback' => (empty($modelsIndividualFeedback)) ? [[new IndividualAssessmentFeedback()]] :  $modelsIndividualFeedback,
        ]);
    }

    /**
     * View individual result.
     * 
     * @return mixed
     */
    public function actionIndividualResult($id)
    {
        $individualAssessmentInfo = IndividualAssessment::findOne($id);
        $workStudentName = $individualAssessmentInfo->studentName;
        $model = $individualAssessmentInfo->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $makerInfos = $individualAssessmentInfo->markerStudentInfos;
        $individualDetails = [];
        foreach ($makerInfos as $index => $makerInfo) {
            $reviewDetails = $makerInfo->individualAssessmentDetails;

            $individualDetails[$index] = $reviewDetails;
        }

        $individualFeedbacks = $individualAssessmentInfo->individualAssessmentFeedbacks;
        $supposedMarkList = [];
        $modelsIndividualFeedback = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $indexItem => $item) {
            
                if ($modelSection->section_type == 0) {

                    $supposedMark = null;

                    if (!empty($individualDetails)) {

                        $totalProposedMark = 0;
                        $count = 0;

                        foreach ($individualDetails as $indexStudent => $reviewDetails) {

                            if (!empty($reviewDetails)) {

                                foreach($reviewDetails as $reviewDetail) {

                                    if ($reviewDetail->item_id == $item->id) {

                                        $totalProposedMark += $reviewDetail->mark;
                                        $count++;
                                        $modelsReviewDetail[$indexSection][$indexItem][$indexStudent] = $reviewDetail;
                                        break;
                                    }
                                }

                            } else {

                                foreach ($makerInfos as $indexStudent => $makerInfo) {
                                    $reviewDetail = new IndividualAssessmentDetail();
                                    $reviewDetail->marker_student_info_id = $makerInfo->id;
                                    $modelsReviewDetail[$indexSection][$indexItem][$indexStudent] = $reviewDetail;
                                }
                            }
                        }

                        if ($totalProposedMark > 0 && $count > 0) {
                            $supposedMark = round($totalProposedMark/$count,0,PHP_ROUND_HALF_DOWN);
                        }
                    }

                    $supposedMarkList[$indexSection][$indexItem] = $supposedMark;
                }

                if (!empty($individualFeedbacks)) {
                    foreach($individualFeedbacks as $individualFeedback) {
                        if ($individualFeedback->item_id == $item->id) {
                            $modelsIndividualFeedback[$indexSection][$indexItem] = $individualFeedback;
                            break;
                        }
                    }
                } else {
                    $individualFeedback = new IndividualAssessmentFeedback();
                    $modelsIndividualFeedback[$indexSection][$indexItem] = $individualFeedback;
                }
            }
        }

        return $this->render('individual-result', [
            'model' => $model,
            'workStudentName' => $workStudentName,
            'supposedMarkList' => $supposedMarkList,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[new IndividualAssessmentDetail()]] :  $modelsReviewDetail,
            'modelsIndividualFeedback' => (empty($modelsIndividualFeedback)) ? [[new IndividualAssessmentFeedback()]] :  $modelsIndividualFeedback,
        ]);
    }

    /**
     * Deletes an existing Assessments model.
     * 
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Assessment has been deleted.');
        return $this->redirect(['dashboard']);
    }

    /**
     * Displays a assessment info.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAssessment($id)
    {
        $model = $this->findModel($id);
        $modelLecturer = new LecturerModel();
        $coorinators = $modelLecturer->getCoordinators($id);

        if ($model->assessment_type == 0 || $model->assessment_type == 1 || $model->assessment_type == 2) {
            $groupInfo = $modelLecturer->getGroupInfo($id);

            return $this->render('assessment', [
                'model' => $model,
                'groupInfo' => $groupInfo,
                'coorinators' => $coorinators,
            ]);
        } else if ($model->assessment_type == 3 || $model->assessment_type == 4) {

            $individualInfo = $modelLecturer->getStudentMarkStatus($id, $model->assessment_type);

            return $this->render('assessment', [
                'model' => $model,
                'individualInfo' => $individualInfo,
                'coorinators' => $coorinators,
            ]);
        }
        
    }

    /**
     * Displays a brief result.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBriefResult($id)
    {
        $model = $this->findModel($id);
        $modelLecturer = new LecturerModel();

        $briefResult = $modelLecturer->getBriefResult($id, $model->assessment_type);

        return $this->render('brief-result', [
            'model' => $model,
            'briefResult' => $briefResult,
        ]);
    }

    /**
     * Mark Group assessment.
     * 
     * @return mixed
     */
    public function actionMarkGroup($id)
    {
        $groupAssessmentInfo = GroupAssessment::findOne($id);

        $groupGrades = $groupAssessmentInfo->groupAssessmentGrades;

        $model = $groupAssessmentInfo->assessment;

        if (empty($groupGrades)) {

            return $this->redirect(['grade-group', 'id' => $id]);
            
        } else {

            $modelsSection = $model->sections;
            $modelsItem = [];
            $modelsReviewDetail = [];
            $groupStudentInfos = $groupAssessmentInfo->groupStudentInfos;

            $totalStudentNumber = count($groupStudentInfos);
            
            $supposedMarkList = [];
            $markerCommentsList = [];
            $contributionList = [];
            $totalProposedMarkList = [];

            $proposedMarks = [];
            $tempComments = [];
            $tempContributions = [];

            foreach ($modelsSection as $indexSection => $modelSection) {

                $items = $modelSection->items;
                $modelsItem[$indexSection] = $items;

                foreach ($items as $indexItem => $item) {
                
                    if ($modelSection->section_type == 0) {

                        foreach ($groupStudentInfos as $indexMarker => $groupStudentInfo) {

                            $reviewDetails = $groupStudentInfo->groupAssessmentDetails;

                            if (!empty($reviewDetails)) {

                                $indexWorker = 0;

                                foreach($reviewDetails as $index => $reviewDetail) {

                                    if ($reviewDetail->item_id == $item->id) {

                                        $modelsReviewDetail[$indexSection][$indexItem][$indexMarker][$indexWorker] = $reviewDetail;

                                        $proposedMarks[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->mark;
                                        $tempComments[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->comment;
                                        $tempContributions[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->contribution;
                                        $indexWorker++;
                                    }
                                }

                            } else {

                                for ($i=0; $i < $totalStudentNumber; $i++) {
                                    $reviewDetail = new GroupAssessmentDetail();
                                    $reviewDetail->group_student_Info_id = $groupStudentInfo->id;
                                    $modelsReviewDetail[$indexSection][$indexItem][$indexMarker][$i] = $reviewDetail;

                                    $proposedMarks[$indexSection][$indexItem][$i][$indexMarker] = null;
                                    $tempComments[$indexSection][$indexItem][$i][$indexMarker] = null;
                                    $tempContributions[$indexSection][$indexItem][$i][$indexMarker] = null;
                                }
                            }

                            $individualFeedback = new GroupAssessmentFeedback();
                            $individualFeedback->item_id = $item->id;
                            $individualFeedback->student_id = $groupStudentInfo->student_id;
                            $individualFeedback->group_id = $id;
                            $modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexMarker] = $individualFeedback;
                        }
                    } else if ($modelSection->section_type == 1) {

                        if ($item->item_type == 0) {
                            foreach ($groupStudentInfos as $indexMarker => $groupStudentInfo) {
                                $individualFeedback = new GroupAssessmentFeedback();
                                $individualFeedback->item_id = $item->id;
                                $individualFeedback->student_id = $groupStudentInfo->student_id;
                                $individualFeedback->group_id = $id;
                                $modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexMarker] = $individualFeedback;
                            }
                        } else if ($item->item_type == 1) {
                            $groupFeedback = new GroupAssessmentFeedback();
                            $groupFeedback->item_id = $item->id;
                            $groupFeedback->group_id = $id;
                            $modelsGroupAssessmentFeedback[$indexSection][$indexItem][0] = $groupFeedback;
                        }
                    }
                }
            }

            if ($model->assessment_type == self::G_PEER_REVIEW || $model->assessment_type == self::G_PEER_R_A) {
                foreach ($tempContributions as $indexSection => $modelsContribution) {
                    foreach ($modelsContribution as $indexItem => $contributions) {
                        foreach ($contributions as $index => $contribution) {

                            $totalC = 0;
                            $count = 0;
    
                            foreach($contribution as $contrib) {
                                if (!empty($contrib)) {
                                    $totalC += $contrib;
                                    $count++;
                                }
                            }
    
                            $contributionList[$indexSection][$indexItem][$index] = $totalC;
                            
                            if ($model->assessment_type == self::G_PEER_REVIEW) {
                                if ($count > 0) {
                                    foreach ($groupGrades as $groupGrade) {
                                        if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                            $supposedMarkList[$indexSection][$indexItem][$index] = round($groupGrade->mark * ($totalC/$count) / (100/$totalStudentNumber),0,PHP_ROUND_HALF_DOWN);
                                        }
                                    }
                                } else {
                                    foreach ($groupGrades as $groupGrade) {
                                        if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                            $supposedMarkList[$indexSection][$indexItem][$index] = $groupGrade->mark;
                                        }
                                    }
                                }
                            }                            
                        }
                    }
                }
            }

            if($model->assessment_type == self::G_PEER_ASSESSMENT || $model->assessment_type == self::G_PEER_R_A) {
                foreach ($proposedMarks as $indexSection => $proposedMark) {
                    foreach ($proposedMark as $indexItem => $marks) {
                        foreach ($marks as $index => $markersMark) {
                            $totalProposedMark = 0;
                            $count = 0;
    
                            foreach($markersMark as $mark) {
                                if (!empty($mark)) {
                                    $totalProposedMark += $mark;
                                    $count++;
                                }
                            }
    
                            $totalProposedMarkList[$indexSection][$indexItem][$index] = $totalProposedMark;

                            if ($count > 0) {
                                if ($model->assessment_type == self::G_PEER_ASSESSMENT) {
                                    $supposedMarkList[$indexSection][$indexItem][$index] = round($totalProposedMark/$count,0,PHP_ROUND_HALF_DOWN);
                                }

                                if ($model->assessment_type == self::G_PEER_R_A) {
                                    $supposedMarkList[$indexSection][$indexItem][$index] = round(($totalProposedMark/$count) * ($contributionList[$indexSection][$indexItem][$index]/$count)  / (100/$totalStudentNumber),0,PHP_ROUND_HALF_DOWN);
                                }
                                
                            } else {
                                
                                foreach ($groupGrades as $groupGrade) {
                                    if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                        $supposedMarkList[$indexSection][$indexItem][$index] = $groupGrade->mark;
                                    }
                                }
                            }
                        }
                    }
                }
            }            

            foreach ($tempComments as $indexSection => $tempComment) {
                foreach ($tempComment as $indexItem => $comments) {

                    foreach ($comments as $index => $commentList) {
                        $tempComment = '';
                        foreach($commentList as $comment) {
                            if (!empty($comment)) {
                                $comment = $tempComment . $comment . " ";
                            }
                        }

                        $markerCommentsList[$indexSection][$indexItem][$index] = $tempComment;
                    }
                }
            }
            
            if ($this->request->isPost) {
                    
                if (isset($_POST['GroupAssessmentFeedback'][0][0][0])) {

                    $totalMark = 0;
                    $valid = true;
                    $markList = [];

                    foreach ($_POST['GroupAssessmentFeedback'] as $indexSection => $groupFeedbacks) {
                    
                        foreach ($groupFeedbacks as $indexItem => $feedbacks) {

                            foreach ($feedbacks as $indexStudent => $feedback) {
                                
                                $data['GroupAssessmentFeedback'] = $feedback;
                                $modelFeedback = new GroupAssessmentFeedback();
                                $modelFeedback->load($data);
                                $modelFeedback->scenario = 'submit';

                                $modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexStudent] = $modelFeedback;
                                
                                // Input validation
                                if($modelFeedback->validate()) {
                                } else {
                                    $valid = false;
                                }
                            }

                        }
                    }

                    $markValidate = new ArrayValidator();

                    if ($markValidate->validateGroupComments($modelsGroupAssessmentFeedback, $markerCommentsList)) {
                    } else {
                        $valid = false;
                    }
                    
                    if($valid) {
                        $transaction = \Yii::$app->db->beginTransaction();

                        try {

                            $flag = true;
                            $count = 0;

                            foreach ($modelsGroupAssessmentFeedback as $indexSection => $groupFeedbacks) {

                                foreach ($groupFeedbacks as $indexItem => $feedbacks) {

                                    if($modelsItem[$indexSection][$indexItem]->item_type == 0) {

                                        foreach ($feedbacks as $indexStudent => $individualFeedback) {

                                            if($modelsSection[$indexSection]->section_type == 0) {
                                                if (empty($individualFeedback->mark)) {
                                                    $individualFeedback->mark = $supposedMarkList[$indexSection][$indexItem][$indexStudent];
                                                }

                                                if (empty($individualFeedback->comment)) {
                                                    $individualFeedback->comment = $markerCommentsList[$indexSection][$indexItem][$indexStudent];
                                                }
                                            }
                                        
                                            $markList[$indexStudent][$count] = $individualFeedback->mark;
                                            
                                            if ($individualFeedback->save(false)) {
                                            } else {
                                                $flag = false;
                                                break;
                                            }
                                        
                                        }

                                        $count++;

                                    } else if($modelsItem[$indexSection][$indexItem]->item_type == 1) {

                                        foreach ($feedbacks as $indexStudent => $individualFeedback) {
                                            
                                            if ($individualFeedback->save(false)) {
                                            } else {
                                                $flag = false;
                                                break;
                                            }
                                        
                                        }

                                        foreach ($groupStudentInfos as $indexWorker => $workStudent) {
                                            $markList[$indexWorker][$count] = $modelsGroupAssessmentFeedback[$indexSection][$indexItem][0]->mark;
                                        }
                                        $count++;
                                    }
                                }
                            }

                            if($flag) {

                                $studentTotalMark = [];

                                foreach ($markList as $indexStudent => $studentMarkList) {
                                    $totalMark = 0;
                                    foreach ($studentMarkList as $mark) {
                                        $totalMark += $mark;
                                    }
                                    $studentTotalMark[$indexStudent] = $totalMark;
                                }

                                foreach ($groupStudentInfos as $index => $workStudent) {
                                    $workStudent->mark = $studentTotalMark[$index];
                                    $workStudent->marked = self::MARKED;

                                    if ($workStudent->save(false)) {
                                    } else {
                                        $flag = false;
                                        break;
                                    }
                                }
                            }
                            
                            if($flag) {

                                $groupAssessmentInfo->marked = self::MARKED;

                                if ($groupAssessmentInfo->save(false)) {
                                } else {
                                    $flag = false;
                                }
                            }

                            if ($flag) {
                                $transaction->commit();
                                Yii::$app->session->setFlash('success', 'Group mark has been successfully updated.');
                                return $this->redirect(['assessment', 'id' => $model->id]);
                            } else {
                                Yii::$app->session->setFlash('error', 'Group mark update failed. Please check your input.');
                                $transaction->rollBack();
                            }
                        } catch (Exception $e) {
                            Yii::$app->session->setFlash('error', 'Group mark update failed. Please check your input.');
                            $transaction->rollBack();
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Input error occurs. Please check your input.');
                    }
                }
            } else {
                $model->loadDefaultValues();
            }

            return $this->render('mark-group', [
                'model' => $model,
                'groupGrades' => $groupGrades,
                'id' => $id,
                'supposedMarkList' => $supposedMarkList,
                'contributionList' => $contributionList,
                'totalProposedMarkList' => $totalProposedMarkList,
                'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
                'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
                'groupStudentInfos' => $groupStudentInfos,
                'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[[[new GroupAssessmentDetail()]]]] :  $modelsReviewDetail,
                'modelsGroupAssessmentFeedback' => (empty($modelsGroupAssessmentFeedback)) ? [[[new GroupAssessmentFeedback()]]] :  $modelsGroupAssessmentFeedback,
            ]);
        }
    }

    /**
     * Mark Group assessment.
     * 
     * @return mixed
     */
    public function actionGroupResult($id)
    {
        $groupAssessmentInfo = GroupAssessment::findOne($id);
        $groupGrades = $groupAssessmentInfo->groupAssessmentGrades;
        
        $model = $groupAssessmentInfo->assessment;

        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $groupStudentInfos = $groupAssessmentInfo->groupStudentInfos;

        $totalStudentNumber = count($groupStudentInfos);
        
        $supposedMarkList = [];
        $markerCommentsList = [];
        $contributionList = [];

        $proposedMarks = [];
        $tempComments = [];
        $tempContributions = [];

        $groupAssessmentFeedbacks = $groupAssessmentInfo->groupAssessmentFeedbacks;

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $indexItem => $item) {
            
                if ($modelSection->section_type == 0) {

                    foreach ($groupStudentInfos as $indexMarker => $groupStudentInfo) {

                        $reviewDetails = $groupStudentInfo->groupAssessmentDetails;

                        if (!empty($reviewDetails)) {

                            $indexWorker = 0;

                            foreach($reviewDetails as $index => $reviewDetail) {

                                if ($reviewDetail->item_id == $item->id) {

                                    $modelsReviewDetail[$indexSection][$indexItem][$indexMarker][$indexWorker] = $reviewDetail;

                                    $proposedMarks[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->mark;
                                    $tempComments[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->comment;
                                    $tempContributions[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail->contribution;
                                    $indexWorker++;
                                }
                            }

                        } else {

                            for ($i=0; $i < $totalStudentNumber; $i++) {
                                $reviewDetail = new GroupAssessmentDetail();
                                $reviewDetail->group_student_Info_id = $groupStudentInfo->id;
                                $modelsReviewDetail[$indexSection][$indexItem][$indexMarker][$i] = $reviewDetail;

                                $proposedMarks[$indexSection][$indexItem][$i][$indexMarker] = null;
                                $tempComments[$indexSection][$indexItem][$i][$indexMarker] = null;
                                $tempContributions[$indexSection][$indexItem][$i][$indexMarker] = null;
                            }
                        }


                        foreach ($groupAssessmentFeedbacks as $feedback) {
                            if ($feedback->item_id == $item->id && $groupStudentInfo->student_id == $groupStudentInfo->student_id) {
                                $modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexMarker] = $feedback;
                                break;
                            }
                        }
                    }
                } else if ($modelSection->section_type == 1) {

                    if ($item->item_type == 0) {
                        foreach ($groupStudentInfos as $indexMarker => $groupStudentInfo) {
                            foreach ($groupAssessmentFeedbacks as $feedback) {
                                if ($feedback->item_id == $item->id && $groupStudentInfo->student_id == $groupStudentInfo->student_id) {
                                    $modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexMarker] = $feedback;
                                    break;
                                }
                            }
                        }
                    } else if ($item->item_type == 1) {
                        foreach ($groupAssessmentFeedbacks as $feedback) {
                            if ($feedback->item_id == $item->id) {
                                $modelsGroupAssessmentFeedback[$indexSection][$indexItem][0] = $feedback;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if($model->assessment_type == self::G_PEER_ASSESSMENT || $model->assessment_type == self::G_PEER_R_A) {
            foreach ($proposedMarks as $indexSection => $proposedMark) {
                foreach ($proposedMark as $indexItem => $marks) {
                    foreach ($marks as $index => $markersMark) {
                        $totalProposedMark = 0;
                        $count = 0;

                        foreach($markersMark as $mark) {
                            if (!empty($mark)) {
                                $totalProposedMark += $mark;
                                $count++;
                            }
                        }

                        if ($count > 0) {
                            $supposedMarkList[$indexSection][$indexItem][$index] = round($totalProposedMark/$count,0,PHP_ROUND_HALF_DOWN);
                        } else {
                            foreach ($groupGrades as $groupGrade) {
                                if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                    $supposedMarkList[$indexSection][$indexItem][$index] = $groupGrade->mark;
                                }
                            }
                        }
                    }
                }
            }
        } else if ($model->assessment_type == self::G_PEER_REVIEW) {
            foreach ($tempContributions as $indexSection => $modelsContribution) {
                foreach ($modelsContribution as $indexItem => $contributions) {
                    foreach ($contributions as $index => $contribution) {

                        $totalC = 0;
                        $count = 0;

                        foreach($contribution as $contrib) {
                            if (!empty($contrib)) {
                                $totalC += $contrib;
                                $count++;
                            }
                        }

                        $contributionList[$indexSection][$indexItem][$index] = $totalC;

                        if ($count > 0) {
                            foreach ($groupGrades as $groupGrade) {
                                if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                    $supposedMarkList[$indexSection][$indexItem][$index] = round($groupGrade->mark * ($totalC/$count) / (100/$totalStudentNumber),0,PHP_ROUND_HALF_DOWN);
                                }
                            }
                        } else {
                            foreach ($groupGrades as $groupGrade) {
                                if ($groupGrade->item_id == $modelsItem[$indexSection][$indexItem]->id) {
                                    $supposedMarkList[$indexSection][$indexItem][$index] = $groupGrade->mark;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($tempComments as $indexSection => $tempComment) {
            foreach ($tempComment as $indexItem => $comments) {

                foreach ($comments as $index => $commentList) {
                    $tempComment = '';
                    foreach($commentList as $comment) {
                        if (!empty($comment)) {
                            $comment = $tempComment . $comment . " ";
                        }
                    }

                    $markerCommentsList[$indexSection][$indexItem][$index] = $tempComment;
                }
            }
        }

        return $this->render('group-result', [
            'model' => $model,
            'id' => $id,
            'groupGrades' => $groupGrades,
            'supposedMarkList' => $supposedMarkList,
            'contributionList' => $contributionList,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'groupStudentInfos' => $groupStudentInfos,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[[[new GroupAssessmentDetail()]]]] :  $modelsReviewDetail,
            'modelsGroupAssessmentFeedback' => (empty($modelsGroupAssessmentFeedback)) ? [[[new GroupAssessmentFeedback()]]] :  $modelsGroupAssessmentFeedback,
        ]);
    }

    /**
     * Grade group.
     * @return mixed
     */
    public function actionGradeGroup($id) {
        $groupAssessmentInfo = GroupAssessment::findOne($id);

        $model = $groupAssessmentInfo->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsGroupAssessmentGrade = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $indexItem => $item) {
                $groupAssessmentGrade = new GroupAssessmentGrade();
                $groupAssessmentGrade->item_id = $item->id;
                $groupAssessmentGrade->group_id = $id;

                $modelsGroupAssessmentGrade[$indexSection][$indexItem] = $groupAssessmentGrade;
            }
        }

        if ($this->request->isPost) {
                    
            if (isset($_POST['GroupAssessmentGrade'][0][0])) {
                $totalMark = 0;
                $valid = true;

                foreach ($_POST['GroupAssessmentGrade'] as $indexSection => $grouprades) {
                    
                    foreach ($grouprades as $indexItem => $grouprade) {
                        
                        $data['GroupAssessmentGrade'] = $grouprade;
                        $modelGroupGrade = new GroupAssessmentGrade();
                        $modelGroupGrade->load($data);
                        $modelGroupGrade->scenario = 'submit';

                        $modelsGroupAssessmentGrade[$indexSection][$indexItem] = $modelGroupGrade;
                        
                        // Input validation
                        if($modelGroupGrade->validate()) {
                        } else {
                            $valid = false;
                        }
                    }
                }
            }

            if($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {

                    $flag = true;
                    $totalMark = 0;
                    
                    foreach ($modelsGroupAssessmentGrade as $grouprades) {
            
                        foreach ($grouprades as $grouprade) {
                            
                            $totalMark += $grouprade->mark;
                            
                            if ($grouprade->save(false)) {
                            } else {
                                $flag = false;
                                break;
                            }
                        }
                    }
                    
                    if($flag) {

                        $groupAssessmentInfo->mark = $totalMark;
                        
                        
                        $flag = $groupAssessmentInfo->save(false);
                    }

                    if ($flag) {
                        $transaction->commit();
                        // echo "<pre>";
                        // print_r($flag);
                        // echo "</pre>";
                        // exit;
                        return $this->redirect(['mark-group', 'id' => $id]);
                    } else {

                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('grade-group', [
            'model' => $model,
            'groupAssessmentInfo' => $groupAssessmentInfo,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsGroupAssessmentGrade' => (empty($modelsGroupAssessmentGrade)) ? [[[new GroupAssessmentGrade()]]] :  $modelsGroupAssessmentGrade,
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

    /**
     * Export results to excel.
     * @return mixed
     */
    public function actionExportResult($id)
    {
        $modelLecturer = new LecturerModel();
        $modelLecturer->getExportData($id);
    }

    /**
     * Send reminder email.
     * @return mixed
     */
    public function actionSendReminder($id)
    {
        $modelLecturer = new LecturerModel();
        $flag = $modelLecturer->sendReminderEmail($id);

        if ($flag) {
            Yii::$app->session->setFlash('success', 'Reminder email has been successfully sent.');
        } else {
            Yii::$app->session->setFlash('error', 'Sending error occurs, check the assessment status.');
        }

        return $this->redirect(['assessment', 'id' => $id]);
    }

    /**
     * Send results to student.
     * @return mixed
     */
    public function actionSendResult($id)
    {
        $modelLecturer = new LecturerModel();
        $flag = $modelLecturer->sendResult($id);

        if ($flag) {
            Yii::$app->session->setFlash('success', 'Results have been successfully sent.');
        }

        return $this->redirect(['assessment', 'id' => $id]);
    }
}

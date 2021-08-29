<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupAssessment;
use common\models\GroupAssessmentDetail;
use common\models\GroupAssessmentFeedback;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessment;
use common\models\IndividualAssessmentDetail;
use common\models\IndividualAssessmentFeedback;
use common\models\IndividualFeedback;
use common\models\Items;
use common\models\MarkerStudentInfo;
use common\models\Rubrics;
use common\models\Sections;
use common\models\User;
use Exception;
use frontend\models\ArrayValidator;
use frontend\models\AssessmentsSearch;

use frontend\models\GroupStudent;
use frontend\models\LecturerModel;
use frontend\models\Model;
use frontend\models\Upload;
use moonland\phpexcel\Excel;
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

    const UNCOMPLETE = 0;
    const COMPLETED = 1;

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
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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
     * Updates an existing Assessments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelsSection = $model->sections;
        $modelsItem = [[new Items()]];
        $oldItems = [];
        $modelsRubric = [[[new Rubrics()]]];
        $oldRubrics = [];
        
        if (!empty($modelsSection[0]->id)) {
            foreach ($modelsSection as $indexSection => $modelSection) {

                $temp_Items = $modelSection->items;
                $modelsItem[$indexSection] = $temp_Items;
                $oldItems = ArrayHelper::merge(ArrayHelper::index($temp_Items, 'id'), $oldItems);

                if (!empty($temp_Items[0]->id)) {
                    foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) {
                        $temp_Rubrics = $modelItem->rubrics;
                        
                        if(!empty($temp_Rubrics[0]->id)) {
                            $modelsRubric[$indexSection][$indexItem] = $temp_Rubrics;
                            $oldRubrics = ArrayHelper::merge(ArrayHelper::index($temp_Rubrics, 'id'), $oldRubrics);
                        } else {
                            $rubric = new Rubrics();
                            $modelsRubric[$indexSection][$indexItem][0] = $rubric;
                        }
                    }
                }
            }
        }

        echo "<pre>";
        print_r($modelsSection);
        print_r($modelsItem);
        print_r($modelsRubric);
        echo "</pre>";
        exit;


        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsRubric' => (empty($modelsRubric)) ? [[[new Rubrics()]]] : $modelsRubric,
        ]);
    }

    /**
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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

                        if ($totalProposedMark > 0 && $count > 0) {
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

                if ($markValidate->validateInputMarks($modelsIndividualFeedback, $supposedMarkList)) {
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
                            return $this->redirect(['assessment', 'id' => $model->id]);
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
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

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
     * Displays a single Branches model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionBriefResult($id)
    {
        $model = $this->findModel($id);
        $inconsistent = [];
        $completed = [];
		$incomplete = [];

        return $this->render('view', [
            'model' => $model,
            'inconsistent' => $inconsistent,
            'completed' => $completed,
            'incomplete' => $incomplete,
        ]);
    }

    /**
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionMarkGroup($id)
    {
        $groupAssessmentInfo = GroupAssessment::findOne($id);

        $model = $groupAssessmentInfo->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $makerInfos = $groupAssessmentInfo->groupStudentInfos;

        $itemBaseDetails = [];
        foreach ($makerInfos as $index => $makerInfo) {
            $reviewDetails = $makerInfo->groupAssessmentDetails;

            $itemBaseDetails[$index] = $reviewDetails;
        }
        
        $modelsIndividualFeedback = [];
        $supposedMarkList = [];
        $markerCommentsList = [];
        $markerInfoList = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $indexItem => $item) {
            
                if ($modelSection->section_type == 0) {

                    if ($item->item_type == 0) {

                        foreach ($makerInfos as $indexWorker => $workerInfo) {

                            if (!empty($itemBaseDetails)) {

                                foreach ($itemBaseDetails as $indexMarker => $reviewDetails) {

                                    if (!empty($reviewDetails)) {

                                        foreach($reviewDetails as $reviewDetail) {

                                            if ($reviewDetail->item_id == $item->id && $reviewDetail->work_student_id == $workerInfo->student_id) {

                                                // $tempComment = $tempComment . $reviewDetail->comment . " ";
                                                $modelsReviewDetail[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail;
                                                break;
                                            }
                                        }

                                    } else {

                                        foreach ($makerInfos as $indexMarker => $makerInfo) {
                                            $reviewDetail = new IndividualAssessmentDetail();
                                            $reviewDetail->marker_student_info_id = $makerInfo->id;
                                            $modelsReviewDetail[$indexSection][$indexItem][$indexWorker][$indexMarker] = $reviewDetail;
                                        }
                                    }
                                }
                            }

                        }

                        $groupIndividualFeedback = new GroupAssessmentFeedback();
                        $groupIndividualFeedback->item_id = $item->id;
                        $groupIndividualFeedback->student_id = $workerInfo->student_id;
                        $groupIndividualFeedback->group_id = $id;
                        $modelsIndividualFeedback[$indexSection][$indexItem][$indexWorker] = $groupIndividualFeedback;

                    } else {

                    }
                }

            }
        }

        if ($this->request->isPost) {
                
            if (isset($_POST['IndividualAssessmentFeedback'][0][0])) {

                $index = 0;
                $totalMark = 0;
                $student_id = $groupAssessmentInfo->student_id;
                $valid = true;

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

                if ($markValidate->validateInputMarks($modelsIndividualFeedback, $supposedMarkList)) {
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
                            return $this->redirect(['assessment', 'id' => $model->id]);
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

        return $this->render('mark-individual', [
            'model' => $model,
            // 'supposedMarkList' => $supposedMarkList,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'makerInfos' => $makerInfos,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[[new IndividualAssessmentDetail()]]] :  $modelsReviewDetail,
            'modelsIndividualFeedback' => (empty($modelsIndividualFeedback)) ? [[new IndividualAssessmentFeedback()]] :  $modelsIndividualFeedback,
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

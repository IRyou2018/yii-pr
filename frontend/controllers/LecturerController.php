<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupAssessment;
use common\models\GroupInfo;
use common\models\GroupStudentInfo;
use common\models\IndividualAssessment;
use common\models\IndividualAssessmentDetail;
use common\models\IndividualFeedback;
use common\models\Items;
use common\models\LecturerAssessment;
use common\models\MarkerStudentInfo;
use common\models\PeerAssessment;
use common\models\PeerReview;
use common\models\PeerReviewDetail;
use common\models\Rubrics;
use common\models\Sections;
use common\models\User;
use Exception;
use frontend\models\AssessmentsSearch;
use frontend\models\CoordinatorsSearch;
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
        $dataProvider = $searchModel->searchByLecturerID($this->request->queryParams);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateActive(){
        $status = Yii::$app->request->post('status');
        $id = Yii::$app->request->post('id');

        $model = $this->findModel($id);

        if($status == "true") {

            $model->active = 1;
        } else {

            $model->active = 0;

        }

        $model->save();
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
                            $modelItem = new Items;
                            $modelItem->load($data);
                            $modelsItem[$indexSection][$indexItem] = $modelItem;
                            $valid = $modelItem->validate();
                        }
                    }
                }

                if (isset($_POST['Rubrics'][0][0][0])) {
                    foreach ($_POST['Rubrics'] as $indexSection => $items) {
                        foreach ($items as $indexItem => $rubrics) {
                            foreach ($rubrics as $indexRubric => $rubric) {
                                $data['Rubrics'] = $rubric;
                                $modelRubric = new Rubrics;
                                $modelRubric->load($data);
                                $modelsRubric[$indexSection][$indexItem][$indexRubric] = $modelRubric;
                                $valid = $modelRubric->validate();
                            }
                        }
                    }
                }

                // Get upload file name
                $modelUpload->file = UploadedFile::getInstance($modelUpload, 'file');
                // Set upload path
                $path = "uploads/";

                // Validate file extension
                $valid = $modelUpload->validate();

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
                    $valid = $modelUpload->validateTemplateFormat($excelData, $model->assessment_type);

                    // Validate file Content
                    $valid = $modelUpload->validateInputContents($excelData, $model->assessment_type);
                }

                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {

                            if ($flag && count($excelData) > 0) {

                                $flag = $this->registDatafromUpload($excelData, $model);
                            }

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
                        }
                    }
                }
            }
        }

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
        $makerInfo = MarkerStudentInfo::findOne($id);
        $model = $makerInfo->individualAssessment->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $reviewDetails = $makerInfo->individualAssessmentDetails;
        $individualFeedbacks = $makerInfo->individualFeedbacks;
        $modelsIndividualFeedback = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {
            
                if ($modelSection->section_type == 0) {

                    if (!empty($reviewDetails)) {
                        foreach($reviewDetails as $reviewDetail) {
                            if ($reviewDetail->item_id == $item->id) {
                                $modelsReviewDetail[$indexSection][$index] = $reviewDetail;
                                break;
                            }
                        }
                    } else {
                        $reviewDetail = new IndividualAssessmentDetail();
                        $modelsReviewDetail[$indexSection][$index] = $reviewDetail;
                    }
                }

                if (!empty($individualFeedbacks)) {
                    foreach($individualFeedbacks as $individualFeedback) {
                        if ($individualFeedback->item_id == $item->id) {
                            $modelsIndividualFeedback[$indexSection][$index] = $individualFeedback;
                            break;
                        }
                    }
                } else {
                    $individualFeedback = new IndividualFeedback();
                    $individualFeedback->item_id = $item->id;
                    $individualFeedback->marker_student_info_id = $id;
                    $modelsIndividualFeedback[$indexSection][$index] = $individualFeedback;
                }
            }
        }

        if ($this->request->isPost) {
                
            if (isset($_POST['IndividualFeedback'][0][0])) {

                $index = 0;
                $individualFeedbacks = [new IndividualFeedback()];
                $actualMark = 0;
                $student_id = $makerInfo->individualAssessment->student_id;
                $valid = true;

                foreach ($_POST['IndividualFeedback'] as $indexSection => $feedbacks) {
                    
                    foreach ($feedbacks as $indexItem => $feedback) {
                        
                        $data['IndividualFeedback'] = $feedback;
                        $modelIndividualFeedback = new IndividualFeedback();
                        $modelIndividualFeedback->load($data);
                        $modelIndividualFeedback->student_id = $student_id;
                        $modelIndividualFeedback->scenario = 'submit';

                        $modelsIndividualFeedback[$indexSection][$indexItem] = $modelIndividualFeedback;
                        
                        // Input validation
                        if($modelIndividualFeedback->validate()) {
                            $actualMark += $modelIndividualFeedback->mark;
                            $individualFeedbacks[$index] = $modelIndividualFeedback;
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
                        
                        foreach ($individualFeedbacks as $index => $individualFeedback) {
                            
                            if ($flag = $individualFeedback->save(false)) {
                            } else {
                                break;
                            }
                        }

                        
                        if($flag) {
                            $individualAssessment = $makerInfo->individualAssessment;

                            $individualAssessment->marked = 1;
                            $individualAssessment->mark_value = $actualMark;

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
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[new IndividualAssessmentDetail()]] :  $modelsReviewDetail,
            'modelsIndividualFeedback' => (empty($modelsIndividualFeedback)) ? [[new IndividualFeedback()]] :  $modelsIndividualFeedback,
        ]);
    }

    /**
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionIndividualResult($id)
    {
        $makerInfo = MarkerStudentInfo::findOne($id);
        $model = $makerInfo->individualAssessment->assessment;
        $modelsSection = $model->sections;
        $modelsItem = [];
        $modelsReviewDetail = [];
        $reviewDetails = $makerInfo->individualAssessmentDetails;
        $individualFeedbacks = $makerInfo->individualFeedbacks;
        $modelsIndividualFeedback = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            foreach ($items as $index => $item) {
            
                if ($modelSection->section_type == 0) {

                    if (!empty($reviewDetails)) {
                        foreach($reviewDetails as $reviewDetail) {
                            if ($reviewDetail->item_id == $item->id) {
                                $modelsReviewDetail[$indexSection][$index] = $reviewDetail;
                                break;
                            }
                        }
                    } else {
                        $reviewDetail = new IndividualAssessmentDetail();
                        $modelsReviewDetail[$indexSection][$index] = $reviewDetail;
                    }
                }

                if (!empty($individualFeedbacks)) {
                    foreach($individualFeedbacks as $individualFeedback) {
                        if ($individualFeedback->item_id == $item->id) {
                            $modelsIndividualFeedback[$indexSection][$index] = $individualFeedback;
                            break;
                        }
                    }
                } else {
                    $individualFeedback = new IndividualFeedback();
                    $modelsIndividualFeedback[$indexSection][$index] = $individualFeedback;
                }
            }
        }

        return $this->render('individual-result', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsReviewDetail' => (empty($modelsReviewDetail)) ? [[new IndividualAssessmentDetail()]] :  $modelsReviewDetail,
            'modelsIndividualFeedback' => (empty($modelsIndividualFeedback)) ? [[new IndividualFeedback()]] :  $modelsIndividualFeedback,
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

        return $this->redirect(['index']);
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

            $individualInfo = $modelLecturer->getStudentMarkStatus($id);

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
                        $modelMarkerStudentInfo->marker_student_id = $modelIndividualAssessment->istudent_idd;

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
}

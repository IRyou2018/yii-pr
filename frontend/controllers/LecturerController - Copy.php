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
    const INACTIVE = 0;
    const ACTIVE = 1;

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
            $model->active = self::ACTIVE;
            $message = 'Assessment status has been set to Visible.';
        } else {
            $model->active = self::INACTIVE;
            $message = 'Assessment status has been set to Invisible.';
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', $message);
        } else {
            Yii::$app->session->setFlash('error', 'Visibility update failed.');
        }

        return $this->redirect('dashboard');
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
        $modelLecturer = new LecturerModel();
        $coordinators = $modelLecturer->getCoordinatorList();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $modelUpload->load($this->request->post())) {

                $modelsSection = Model::createMultiple(Sections::classname());
                Model::loadMultiple($modelsSection, Yii::$app->request->post());

                // validate all models
                $valid = $model->validate();
                $valid = Model::validateMultiple($modelsSection) && $valid;

                if (isset($_POST['Items'])) {
                    
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


                if (isset($_POST['Rubrics'])) {
                    
                    foreach ($_POST['Rubrics'] as $indexSection => $modelsRubric) {
                        foreach ($modelsRubric as $indexItem => $rubrics) {
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
                echo "<pre>";
                print_r($modelsItem);
                echo "</pre>";
                exit;
                
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
                            $rubric = [new Rubrics()];
                            $modelsRubric[$indexSection][$indexItem] = $rubric;
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
}

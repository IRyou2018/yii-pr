<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\Items;
use common\models\PeerAssessment;
use common\models\PeerAssessmentDetail;
use common\models\PeerReview;
use common\models\PeerReviewDetail;
use common\models\Rubrics;
use common\models\Sections;
use Exception;
use frontend\models\AssessmentsSearch;
use frontend\models\StudentModel;
use frontend\models\Upload;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * LecturerController
 */
class StudentController extends Controller
{
    const PEER_ASSESS = 0;
    const PEER_REVIEW = 1;

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
     * Creates a new Assessments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSubmit($id, $assessment_id)
    {
        $model = $this->findModel($assessment_id);
        $assessment_type = $model->assessment_type;

        $section = new Sections();
        $modelsSection = $section->getStudentSections($assessment_id);
        $modelsItem = [[new Items()]];
        $modelsPeerAssessmentDetail = [];
        $modelsPeerReviewDetail = [];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            $studentModel = new StudentModel();
            // Peer Assessment
            if ($assessment_type == self::PEER_ASSESS) {

                foreach ($items as $index => $item) {
                    $modelPADetail = new PeerAssessmentDetail();
                    $modelPADetail->item_id = $item->id;
                    $modelPADetail->peer_assessment_id = $id;

                    $modelsPeerAssessmentDetail[$indexSection][$index] = $modelPADetail;
                }
            } 
            // Peer Review
            else if ($assessment_type == self::PEER_REVIEW) {

                foreach ($items as $index => $item) {
                    $modelPRDetail = new PeerReviewDetail();
                    $modelPRDetail->item_id = $item->id;
                    $modelPRDetail->peer_review_id = $id;

                    $modelsPeerReviewDetail[$indexSection][$index] = $modelPRDetail;
                }
            }
        }

        // Peer Assessment
        if ($assessment_type == self::PEER_ASSESS) {
            if ($this->request->isPost) {
                
                if (isset($_POST['PeerAssessmentDetail'][0][0])) {

                    $index = 0;
                    $paDetails = [];
                    $valid = true;

                    // Get Input value
                    foreach ($_POST['PeerAssessmentDetail'] as $indexSection => $peerAssessmentDetails) {
                        
                        foreach ($peerAssessmentDetails as $indexItem => $peerAssessmentDetail) {
                            
                            $data['PeerAssessmentDetail'] = $peerAssessmentDetail;
                            $peerAssessmentDetail = new PeerAssessmentDetail();
                            $peerAssessmentDetail->load($data);
                            $peerAssessmentDetail->scenario = 'submit';

                            $modelsPeerAssessmentDetail[$indexSection][$indexItem] = $peerAssessmentDetail;

                            // Input validation
                            if($peerAssessmentDetail->validate()) {
                                $paDetails[$index] = $peerAssessmentDetail;
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

                            foreach ($paDetails as $peerAssessmentDetail) {

                                if ($flag = $peerAssessmentDetail->save(false)) {
                                } else {
                                    break;
                                }
                            }

                            if($flag) {
                                $peerAssessment = PeerAssessment::findOne($id);

                                $peerAssessment->completed = self::COMPLETED;

                                $flag = $peerAssessment->save(false);
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
        }
        // Peer Assessment
        else if ($assessment_type == self::PEER_REVIEW) {
            
            if ($this->request->isPost) {
                
                if (isset($_POST['PeerReviewDetail'][0][0])) {

                    $index = 0;
                    $prDetails = [];
                    $valid = true;

                    // Get Input value
                    foreach ($_POST['PeerReviewDetail'] as $indexSection => $peerReviewDetails) {
                        
                        foreach ($peerReviewDetails as $indexItem => $peerReviewDetail) {
                            
                            $data['PeerReviewDetail'] = $peerReviewDetail;
                            $modelPeerReviewDetail = new PeerReviewDetail();
                            $modelPeerReviewDetail->load($data);
                            $modelPeerReviewDetail->scenario = 'submit';

                            $modelsPeerReviewDetail[$indexSection][$indexItem] = $modelPeerReviewDetail;

                            // Input validation
                            if($modelPeerReviewDetail->validate()) {
                                $prDetails[$index] = $modelPeerReviewDetail;
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

                            foreach ($prDetails as $index => $peerReviewDetail) {

                                if ($flag = $peerReviewDetail->save(false)) {
                                } else {
                                    break;
                                }
                            }

                            if($flag) {
                                $peerReview = PeerReview::findOne($id);

                                $peerReview->completed = self::COMPLETED;

                                $flag = $peerReview->save(false);
                                // echo '<pre>';
                                // print_r($flag);
                                // // print_r($peerReviewDetail);
                                // // print_r($peerReviewDetail->save(false));
                                // echo '</pre>';
                                // die;
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
        }

        return $this->render('submit', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
            'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
            'modelsPeerAssessmentDetail' => (empty($modelsPeerAssessmentDetail)) ? [[[new PeerAssessmentDetail()]]] :  $modelsPeerAssessmentDetail,
            'modelsPeerReviewDetail' => (empty($modelsPeerReviewDetail)) ? [[new PeerReviewDetail()]] :  $modelsPeerReviewDetail,
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

<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\Items;
use common\models\PeerAssessmentDetail;
use common\models\PeerReview;
use common\models\PeerReviewDetail;
use common\models\Rubrics;
use common\models\Sections;
use Exception;
use frontend\models\AssessmentsSearch;
use frontend\models\StudentModel;
use frontend\models\Upload;
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
    const COMPLETED = 1;
    const PEER_ASSESS = 0;
    const PEER_REVIEW = 1;
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
        $unCompletedAssessment = $searchModel->searchUncompleted();
        $completedAssessment = $searchModel->searchCompleted();
        $feedbacks = $searchModel->searchFeedbacks();

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
    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        $assessment_type = $model->assessment_type;

        $section = new Sections();
        $modelsSection = $section->getStudentSections($id);
        $modelsItem = [[new Items()]];
        $modelsPeerAssessmentDetail = [[new PeerAssessmentDetail()]];
        $modelsPeerReviewDetail = [[new PeerReviewDetail()]];

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            $studentModel = new StudentModel();
            // Peer Assessment
            if ($assessment_type == self::PEER_ASSESS) {

                $peerAssessmentID = $studentModel->getPeerAssessmentId($id);

                $modelsPeerAssessmentDetail = [];
                foreach ($items as $index => $item) {
                    $modelPADetail = new PeerAssessmentDetail();
                    $modelPADetail->item_id = $item->id;
                    $modelPADetail->peer_assessment_id = $peerAssessmentID;
                }
            } 
            // Peer Review
            else if ($assessment_type == self::PEER_REVIEW) {

                $peerReviewID = $studentModel->getPeerReviewId($id);

                foreach ($items as $index => $item) {
                    $modelPRDetail = new PeerReviewDetail();
                    $modelPRDetail->item_id = $item->id;
                    $modelPRDetail->peer_review_id = $peerReviewID;

                    $modelsPeerReviewDetail[$indexSection][$index] = $modelPRDetail;
                }
            }
        }

        // Peer Assessment
        if ($assessment_type == self::PEER_ASSESS) {

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
                                $peerReview = PeerReview::findOne($peerReviewID);

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
            'modelsPeerAssessmentDetail' => (empty($modelsPeerAssessmentDetail)) ? [[new PeerAssessmentDetail()]] :  $modelsPeerAssessmentDetail,
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

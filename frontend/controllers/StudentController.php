<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\Items;
use common\models\PeerAssessmentDetail;
use common\models\PeerReviewDetail;
use common\models\Rubrics;
use common\models\Sections;
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

        // $items = Items::find()
        //     ->join('INNER JOIN', 'sections', 'items.section_id = sections.id')
        //     ->join('INNER JOIN', 'assessments', 'sections.assessment_id = assessments.id')
        //     ->where('assessments.id = :id')
        //     ->addParams([':id' => $id])
        //     ->all();

        // echo "<pre>";
        // print_r($modelsSection);
        // echo "</pre>";
        // exit;

        foreach ($modelsSection as $indexSection => $modelSection) {

            $items = $modelSection->items;
            $modelsItem[$indexSection] = $items;

            $studentModel = new StudentModel();
            // Peer Assessment
            if ($assessment_type == 0) {

                $peerAssessmentID = $studentModel->getPeerAssessmentId($id);

                $modelsPeerAssessmentDetail = [];
                foreach ($items as $index => $item) {
                    $modelPADetail = new PeerAssessmentDetail();
                    $modelPADetail->item_id = $item->id;
                    $modelPADetail->peer_assessment_id = $peerAssessmentID;
                }
            } 
            // Peer Review
            else if ($assessment_type == 1) {

                $peerReviewID = $studentModel->getPeerReviewId($id);

                foreach ($items as $index => $item) {
                    $modelPRDetail = new PeerReviewDetail();
                    $modelPRDetail->item_id = $item->id;
                    $modelPRDetail->peer_review_id = $peerReviewID;

                    $modelsPeerReviewDetail[$indexSection][$index] = $modelPRDetail;
                }
            }
        }
        // echo "<pre>";
        // // print_r($modelsItem);
        // print_r($modelsPeerReviewDetail);
        // echo "</pre>";
        // exit;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

            }
        } else {
            $model->loadDefaultValues();
        }

        // if($assessment_type == 0) {
        //     return $this->render('peer-assessment', [
        //         'model' => $model,
        //         'modelsSection' => $modelsSection,
        //         'modelsItem' => $modelsItem,
        //         'modelsPeerAssessmentDetail' => $modelsPeerAssessmentDetail,
        //         // 'peerAssessmentID' => $peerAssessmentID,
        //     ]);
        // }
        // else if ($assessment_type ==1) {
        //     return $this->render('peer-review', [
        //         'model' => $model,
        //         'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
        //         'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
        //         'modelsPeerReviewDetail' => (empty($modelsPeerReviewDetail)) ? [[new PeerReviewDetail()]] :  $modelsPeerReviewDetail,
        //         // 'peerReviewID' => $peerReviewID,
        //     ]);
        // }
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

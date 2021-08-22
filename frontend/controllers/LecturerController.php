<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupInfo;
use common\models\IndividualAssessment;
use common\models\Items;
use common\models\LecturerAssessment;
use common\models\PeerAssessment;
use common\models\PeerReview;
use common\models\Rubrics;
use common\models\Sections;
use common\models\User;
use Exception;
use frontend\models\AssessmentsSearch;
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
                                    print_r($coordinatorList);
                                    print_r(Yii::$app->request->post('selection'));
                                    // die();
                                    if (!empty($coordinatorList)) {
                                        foreach ($coordinatorList as $coorinator) {
                                            print_r("A");
                                            // die();
                                            $modelLecturerAssessment = new LecturerAssessment();
                                            $modelLecturerAssessment->assessment_id = $model->id;
                                            $modelLecturerAssessment->lecturer_id = $coorinator;

                                            if ($flag = $modelLecturerAssessment->save(false)) {
                                            } else {
                                                break;
                                            }
                                        }
                                    }
                                    print_r("B");
                                    // die();
                                }
                            }
                        }
                        if ($flag) {
                            $transaction->commit();
                            return $this->redirect(['../lecturer/dashboard']);
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

        // echo "<pre>";
        // print_r($modelsSection);
        // print_r($modelsItem);
        // print_r($modelsRubric);
        // echo "</pre>";
        // exit;

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
        
        // For peer assessment (Group)
        if ($model->assessment_type == 0) {

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

                        $modelPeerAssessment = new PeerAssessment();
                        $modelPeerAssessment->group_id = $temp_Group_id;
                        $modelPeerAssessment->marked = 0;
                        $modelPeerAssessment->completed = 0;

                        // If student not exist, regist
                        if (empty($student)) {

                            $modelUser->first_name = $sortedData[$i]['First Name'];
                            $modelUser->last_name = $sortedData[$i]['Last Name'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                            $modelUser->email = $sortedData[$i]['Email'];
                            $modelUser->type = '1';
                            $modelUser->generateAuthKey();

                            if($flag = $modelUser->save(false)) {
                                $modelPeerAssessment->student_id = $modelUser->id;
                            }
                        } else {
                            $modelPeerAssessment->student_id = $student->id;
                        }

                        if ($flag = $modelPeerAssessment->save(false)) {
                        } else {
                            break;
                        }
                    } else {
                        // Regist new gourp
                        $temp_Group_name = $sortedData[$i]['Group Name'];;

                        $modelGroupInfo = new GroupInfo();

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

                            $modelPeerAssessment = new PeerAssessment();
                            $modelPeerAssessment->group_id = $temp_Group_id;
                            $modelPeerAssessment->marked = 0;
                            $modelPeerAssessment->completed = 0;
                            
                            // If student not exist, regist
                            if (empty($student)) {
                                $modelUser->first_name = $sortedData[$i]['First Name'];
                                $modelUser->last_name = $sortedData[$i]['Last Name'];
                                $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                $modelUser->email = $sortedData[$i]['Email'];
                                $modelUser->type = '1';
                                $modelUser->generateAuthKey();
                                
                                if($flag = $modelUser->save(false)) {
                                    $modelPeerAssessment->student_id = $modelUser->id;
                                }
                            } else {
                                $modelPeerAssessment->student_id = $student->id;
                            }

                            if ($flag = $modelPeerAssessment->save(false)) {
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
        // For peer review (Individual)
        else if ($model->assessment_type == 1) {
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

                        $modelPeerReview = new PeerReview();
                        $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;
                        $modelPeerReview->completed = 0;

                        // If student not exist, regist
                        if (empty($student)) {
                            
                            $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                            $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                            $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                            $modelUser->type = '1';
                            $modelUser->generateAuthKey();

                            if($flag = $modelUser->save(false)) {
                                $modelPeerReview->marker_student_id = $modelUser->id;
                            }
                        } else {
                            $modelPeerReview->marker_student_id = $student->id;
                        }

                        if ($flag = $modelPeerReview->save(false)) {
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

                        // If student not exist, regist
                        if (empty($student)) {

                            $modelUser->first_name = $sortedData[$i]['First Name'];
                            $modelUser->last_name = $sortedData[$i]['Last Name'];
                            $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                            $modelUser->email = $sortedData[$i]['Email'];
                            $modelUser->type = '1';
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

                            $modelPeerReview = new PeerReview();
                            $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;
                            $modelPeerReview->completed = 0;

                            // Student not exists, regist new student.
                            if (empty($student)) {
                                
                                $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                                $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                                $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                                $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                                $modelUser->type = '1';
                                $modelUser->generateAuthKey();

                                if($flag = $modelUser->save(false)) {
                                    $modelPeerReview->marker_student_id = $modelUser->id;
                                }
                            } else {
                                $modelPeerReview->marker_student_id = $student->id;
                            }

                            if ($flag = $modelPeerReview->save(false)) {
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

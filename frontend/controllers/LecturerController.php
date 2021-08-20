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
     * Lists all Assessments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AssessmentsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Assessments model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
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

    public function actionDownload($assessment_type)
    {
        // print_r($assessment_type);
        // die();
        $path = Yii::getAlias('@webroot') . '/uploads';

        if ($assessment_type == 0) {
            $file = '/PeerAssessmentTemplate.xlsx';
            $root = $path . $file;
        } else if ($assessment_type == 1) {
            $file = $path . '/PeerReviewTemplate.xlsx';
        }

        if (file_exists($root)) {
            return Yii::$app->response->sendFile($root);
        } else {
            throw new \yii\web\NotFoundHttpException("{$file} is not found!");
        }
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
                    // echo '<pre>';
                    // print_r($valid);
                    // echo '</pre>';
                    // die();
                    // Validate file Content
                    $valid = $modelUpload->validateInputContents($excelData, $model->assessment_type);
                }

                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {

                            if ($flag && count($excelData) > 0) {

                                $i = 0;

                                // echo '<pre>';
                                // print_r($excelData);
                                // echo '</pre>';
                                // die();

                                // For peer assessment (Group)
                                if ($model->assessment_type == 0) {

                                    $sortedData = $this->arraySort($excelData, 'Group Name', SORT_ASC);

                                    $temp_Group_name = '';
                                    $temp_Group_id = '';

                                    do {
                                        print_r("1");
                                        // die();
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

                                                // If student not exist, regist
                                                if (!$modelUser->findByEmail($sortedData[$i]['Email'])) {

                                                    $modelUser->first_name = $sortedData[$i]['First Name'];
                                                    $modelUser->last_name = $sortedData[$i]['Last Name'];
                                                    $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                                    $modelUser->email = $sortedData[$i]['Email'];
                                                    $modelUser->type = '1';
                                                    $modelUser->generateAuthKey();
                                                    $modelUser->save();
                                                }

                                                $modelPeerAssessment = new PeerAssessment();
                                                $modelPeerAssessment->student_id = $modelUser->id;
                                                $modelPeerAssessment->group_id = $temp_Group_id;

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

                                                if ($flag = $modelGroupInfo->save(false)) {

                                                    $temp_Group_id = $modelGroupInfo->id;

                                                    $modelUser = new User();

                                                    // If student not exist, regist
                                                    if (!$modelUser->findByEmail($sortedData[$i]['Email'])) {
                                                        $modelUser->first_name = $sortedData[$i]['First Name'];
                                                        $modelUser->last_name = $sortedData[$i]['Last Name'];
                                                        $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                                        $modelUser->email = $sortedData[$i]['Email'];
                                                        $modelUser->type = '1';
                                                        $modelUser->generateAuthKey();
                                                        $modelUser->save();
                                                    }

                                                    $modelPeerAssessment = new PeerAssessment();
                                                    $modelPeerAssessment->student_id = $modelUser->id;
                                                    $modelPeerAssessment->group_id = $temp_Group_id;

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
                                    // die();
                                }
                                // For peer review (Individual)
                                else if ($model->assessment_type == 1) {
                                    $sortedData = $this->arraySort($excelData, 'Email', SORT_ASC);
                                    $temp_student_email = '';
                                    $temp_individual_assessment_id = '';

                                    // echo '<pre>';
                                    // print_r($sortedData);
                                    // echo '</pre>';
                                    // die();

                                    do {
                                        print_r("1");
                                        // die();
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
                                            print_r("2");
                                            continue;
                                        } else {
                                            print_r("3");
                                            // die();
                                            // For same work student, regist peer review (Marker Student)
                                            if (!empty($temp_student_email) && $sortedData[$i]['Email'] == $temp_student_email) {
                                                print_r("4");
                                                $modelUser = new User();

                                                if (!$modelUser->findByEmail($sortedData[$i]['Email(Marker Student)'])) {
                                                    print_r("5");
                                                    $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                                                    $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                                                    $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                                                    $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                                                    $modelUser->type = '1';
                                                    $modelUser->generateAuthKey();
                                                    $modelUser->save();
                                                }

                                                $modelPeerReview = new PeerReview();
                                                $modelPeerReview->marker_student_id = $modelUser->id;
                                                $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;

                                                if ($flag = $modelPeerReview->save(false)) {
                                                } else {
                                                    break;
                                                }
                                            } else {
                                                print_r("6");
                                                // die();
                                                // Regist new work student assessment info
                                                $temp_student_email = $sortedData[$i]['Email'];

                                                $modelUser = new User();
                                                // print_r($temp_student_email);
                                                if ($user = $modelUser->findByEmail($temp_student_email)) {

                                                    print_r("7");
                                                    // die();
                                                    $modelUser->id = $user->id;
                                                } else {

                                                    $modelUser->first_name = $sortedData[$i]['First Name'];
                                                    $modelUser->last_name = $sortedData[$i]['Last Name'];
                                                    $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                                    $modelUser->email = $sortedData[$i]['Email'];
                                                    $modelUser->type = '1';
                                                    $modelUser->generateAuthKey();
                                                    $modelUser->save();
                                                }

                                                $modelIndividualAssessment = new IndividualAssessment();
                                                
                                                $modelIndividualAssessment->student_id = $modelUser->id;
                                                $modelIndividualAssessment->file_path = $sortedData[$i]['Work File'];;

                                                $modelIndividualAssessment->assessment_id = $model->id;

                                                if ($flag = $modelIndividualAssessment->save(false)) {

                                                } else {
                                                    print_r("9");
                                                    die();
                                                    $temp_individual_assessment_id = $modelIndividualAssessment->id;

                                                    $modelUser = new User();

                                                    if (!$modelUser->findByEmail($sortedData[$i]['Email(Marker Student)'])) {
                                                        print_r("10");
                                                        $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
                                                        $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
                                                        $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
                                                        $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
                                                        $modelUser->type = '1';
                                                        $modelUser->generateAuthKey();
                                                        $modelUser->save();
                                                    }

                                                    $modelPeerReview = new PeerReview();
                                                    $modelPeerReview->marker_student_id = $modelUser->id;
                                                    $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;

                                                    if ($flag = $modelPeerReview->save(false)) {
                                                    } else {
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        $i++;
                                    } while ($i < count($sortedData));
                                    // die();
                                }
                                // $flag = $this->registDatafromUpload($excelData, $model);
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
                                    if(!empty($coordinatorList)) {
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

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
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

        // $i = 0;

        // // echo '<pre>';
        // // print_r($excelData);
        // // echo '</pre>';
        // // die();

        // // For peer assessment (Group)
        // if($model->assessment_type = 0) {

        //     $sortedData = $this->arraySort($excelData, 'Group Name', SORT_ASC);

        //     $temp_Group_name = '';
        //     $temp_Group_id = '';

        //     do {
        //         // Skip empty row
        //         if(empty($sortedData[$i]['Group Name'])
        //             && empty($sortedData[$i]['First Name']) 
        //             && empty($sortedData[$i]['Last Name']) 
        //             && empty($sortedData[$i]['Matriculation Number']) 
        //             && empty($sortedData[$i]['Email'])) {

        //             continue;
        //         } else {

        //             // For same group add Student to group
        //             if(!empty($temp_Group_name) && $sortedData[$i]['Group Name'] == $temp_Group_name) {

        //                 $modelUser = new User();

        //                 // If student not exist, regist
        //                 if(!$modelUser->findByEmail($sortedData[$i]['Email'])) {

        //                     $modelUser->first_name = $sortedData[$i]['First Name'];
        //                     $modelUser->last_name = $sortedData[$i]['Last Name'];
        //                     $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
        //                     $modelUser->email = $sortedData[$i]['Email'];
        //                     $modelUser->type = '1';
        //                     $modelUser->generateAuthKey();
        //                     $modelUser->save();
        //                 }

        //                 $modelPeerAssessment = new PeerAssessment();
        //                 $modelPeerAssessment->student_id = $modelUser->id;
        //                 $modelPeerAssessment->group_id = $temp_Group_id;

        //                 if(!($flag = $modelPeerAssessment->save(false))) {

        //                     break;
        //                 }

        //             } else {

        //                 // Regist new gourp
        //                 $temp_Group_name = $sortedData[$i]['Group Name'];;

        //                 $modelGroupInfo = new GroupInfo();

        //                 $modelGroupInfo->name = $temp_Group_name;
        //                 $modelGroupInfo->assessment_id = $model->id;

        //                 if($flag = $modelGroupInfo->save(false)) {

        //                     $temp_Group_id = $modelGroupInfo->id;

        //                     $modelUser = new User();

        //                     // If student not exist, regist
        //                     if(!$modelUser->findByEmail($sortedData[$i]['Email'])) {
        //                         $modelUser->first_name = $sortedData[$i]['First Name'];
        //                         $modelUser->last_name = $sortedData[$i]['Last Name'];
        //                         $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
        //                         $modelUser->email = $sortedData[$i]['Email'];
        //                         $modelUser->type = '1';
        //                         $modelUser->generateAuthKey();
        //                         $modelUser->save();
        //                     }

        //                     $modelPeerAssessment = new PeerAssessment();
        //                     $modelPeerAssessment->student_id = $modelUser->id;
        //                     $modelPeerAssessment->group_id = $temp_Group_id;

        //                     if(!($flag = $modelPeerAssessment->save(false))) {
        //                         break;
        //                     }
        //                 } else {
        //                     $flag = false;
        //                     break;
        //                 }
        //             }
        //         }

        //         $i++;
        //     } while ($i < count($sortedData));
        // }
        // // For peer review (Individual)
        // else if ($model->assessment_type = 1) {
        //     $sortedData = $this->arraySort($excelData, 'Email', SORT_ASC);
        //     $temp_student_email = '';
        //     $temp_individual_assessment_id = '';

        //     // echo '<pre>';
        //     // print_r($sortedData);
        //     // echo '</pre>';
        //     // die();

        //     do {
        //         print_r("1");
        //         // Skip empty row
        //         if(empty($sortedData[$i]['First Name']) 
        //             && empty($sortedData[$i]['Last Name']) 
        //             && empty($sortedData[$i]['Matriculation Number']) 
        //             && empty($sortedData[$i]['Email'])
        //             && empty($sortedData[$i]['Work File'])
        //             && empty($sortedData[$i]['First Name(Marker Student)']) 
        //             && empty($sortedData[$i]['Last Name(Marker Student)']) 
        //             && empty($sortedData[$i]['Matriculation Number(Marker Student)']) 
        //             && empty($sortedData[$i]['Email(Marker Student)'])) {
        //                 print_r("2");
        //             continue;
        //         } else {
        //             print_r("3");
        //             // die();
        //             // For same work student, regist peer review (Marker Student)
        //             if(!empty($temp_student_email) && $sortedData[$i]['Email'] == $temp_student_email) {
        //                 print_r("4");
        //                 $modelUser = new User();

        //                 if(!$modelUser->findByEmail($sortedData[$i]['Email(Marker Student)'])) {
        //                     print_r("5");
        //                     $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
        //                     $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
        //                     $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
        //                     $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
        //                     $modelUser->type = '1';
        //                     $modelUser->generateAuthKey();
        //                     $modelUser->save();
        //                 }

        //                 $modelPeerReview = new PeerReview();
        //                 $modelPeerReview->marker_student_id = $modelUser->id;
        //                 $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;

        //                 if(!($flag = $modelPeerReview->save(false))) {
        //                     print_r("6");
        //                     break;
        //                 }

        //             } else {

        //                 // Regist new work student assessment info
        //                 $temp_student_email = $sortedData[$i]['Email'];

        //                 $modelUser = new User();
        //                 // print_r($temp_student_email);
        //                 if($user = $modelUser->findByEmail($temp_student_email)) {
        //                     $modelUser->id = $user->id;

        //                 } else {

        //                     $modelUser->first_name = $sortedData[$i]['First Name'];
        //                     $modelUser->last_name = $sortedData[$i]['Last Name'];
        //                     $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
        //                     $modelUser->email = $sortedData[$i]['Email'];
        //                     $modelUser->type = '1';
        //                     $modelUser->generateAuthKey();
        //                     $modelUser->save();
        //                 }


        //                 $modelIndividualAssessment = new IndividualAssessment();

        //                 // echo '<pre>';
        //                 // print_r($sortedData[$i]);
        //                 // print_r($modelUser->id);
        //                 // print_r($model->id);
        //                 // echo '</pre>';
        //                 // die();
        //                 $file_path = $sortedData[$i]['File'];
        //                 echo '<pre>';
        //                 print_r($file_path);
        //                 echo '</pre>';
        //                 die();
        //                 $modelIndividualAssessment->student_id = $modelUser->id;
        //                 // $modelIndividualAssessment->file_path = $file_path;

        //                 $modelIndividualAssessment->assessment_id = $model->id;

        //                 echo '<pre>';
        //                 print_r($modelIndividualAssessment);
        //                 echo '</pre>';
        //                 die();

        //                 if($flag = $modelIndividualAssessment->save(false)) {
        //                     echo '<pre>';
        //                     print_r($flag);
        //                     print_r($modelIndividualAssessment);
        //                     echo '</pre>';
        //                     die();
        //                 } else {
        //                     print_r("9");
        //                     die();
        //                     $temp_individual_assessment_id = $modelIndividualAssessment->id;

        //                     $modelUser = new User();

        //                     if(!$modelUser->findByEmail($sortedData[$i]['Email(Marker Student)'])) {
        //                         print_r("10");
        //                         $modelUser->first_name = $sortedData[$i]['First Name(Marker Student)'];
        //                         $modelUser->last_name = $sortedData[$i]['Last Name(Marker Student)'];
        //                         $modelUser->matric_number = $sortedData[$i]['Matriculation Number(Marker Student)'];
        //                         $modelUser->email = $sortedData[$i]['Email(Marker Student)'];
        //                         $modelUser->type = '1';
        //                         $modelUser->generateAuthKey();
        //                         $modelUser->save();
        //                     }

        //                     $modelPeerReview = new PeerReview();
        //                     $modelPeerReview->marker_student_id = $modelUser->id;
        //                     $modelPeerReview->individual_assessment_id = $temp_individual_assessment_id;

        //                     if(!($flag = $modelPeerReview->save(false))) {
        //                         print_r("11");
        //                         die();
        //                         break;
        //                     }
        //                 }
        //             }
        //         }

        //         $i++;
        //     } while ($i < count($sortedData));
        // }
        return $flag;
    }
}

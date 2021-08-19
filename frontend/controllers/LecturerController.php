<?php

namespace frontend\controllers;

use common\models\Assessments;
use common\models\GroupInfo;
use common\models\Items;
use common\models\LecturerAssessment;
use common\models\PeerAssessment;
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
        $modelLecturerAssessment = new LecturerAssessment();
        $coordinators = User::findCoordinators(Yii::$app->user->id);
        $coordinators = ArrayHelper::map($coordinators, 'id', function($array, $default){return $array['first_name'] . ' '. $array['last_name'];});
        // echo '<pre>';
        // // print_r($coordinators);
        // print_r($coordinators);
        // echo '</pre>';
        // die();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $modelUpload->load($this->request->post()) && $modelLecturerAssessment->load($this->request->post())) {

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

                $modelUpload->file = UploadedFile::getInstance($modelUpload, 'file');
                $path = "uploads/";

                $valid = $modelUpload->validate();

                if($valid) {
                    if(!file_exists($path)) {
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

                    $valid = $modelUpload->validateGroupTemplateFormat($excelData);

                    $valid = $modelUpload->validateInputContents($excelData);
                }
                
                if ($valid) {
                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        if ($flag = $model->save(false)) {

                            if($flag && count($excelData) > 0) {
                                print_r(" 1 ");
                                
                                $sortedData = $this->arraySort($excelData, 'Group Name', SORT_ASC);

                                $i = 0;

                                $temp_Group_name = '';
                                $temp_Group_id = '';

                                do {
                                    print_r(" 2 ");
                                    if(empty($sortedData[$i]['Group Name'])
                                        && empty($sortedData[$i]['First Name']) 
                                        && empty($sortedData[$i]['Last Name']) 
                                        && empty($sortedData[$i]['Matriculation Number']) 
                                        && empty($sortedData[$i]['Email'])) {

                                        continue;
                                    } else {
                                        if(!empty($temp_Group_name) && $sortedData[$i]['Group Name'] == $temp_Group_name) {
                                            print_r(" 3 ");
                                            // echo '<pre>';
                                            // print_r($sortedData);
                                            // echo '</pre>';
                                            // die();
                                            $modelUser = new User();

                                            if($modelUser->findByEmail($sortedData[$i]['Email'])) {
                                                print_r(" 4 ");
                                            } else {
                                                $modelUser->first_name = $sortedData[$i]['First Name'];
                                                $modelUser->last_name = $sortedData[$i]['Last Name'];
                                                $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                                $modelUser->email = $sortedData[$i]['Email'];
                                                $modelUser->type = '1';
                                                $modelUser->generateAuthKey();
                                                $modelUser->save();
                                                print_r(" 5 ");
                                            }

                                            $modelPeerAssessment = new PeerAssessment();
                                            $modelPeerAssessment->student_id = $modelUser->id;
                                            $modelPeerAssessment->group_id = $temp_Group_id;
                                            
                                            if($flag = $modelPeerAssessment->save(false)) {
                                                print_r(" 6 ");
                                            } else {
                                                print_r(" 7 ");
                                                $flag = false;
                                                break;
                                            }

                                        } else {
                                            print_r(" 8 ");
                                            
                                            $temp_Group_name = $sortedData[$i]['Group Name'];;

                                            $modelGroupInfo = new GroupInfo();

                                            $modelGroupInfo->name = $temp_Group_name;
                                            $modelGroupInfo->assessment_id = $model->id;

                                            // echo '<pre>';
                                            // print_r($flag = $modelGroupInfo->save(false));
                                            // echo '</pre>';
                                            // die();
                                            if($flag = $modelGroupInfo->save(false)) {
                                                print_r(" 9 ");
                                                $temp_Group_id = $modelGroupInfo->id;

                                                $modelUser = new User();

                                                if($modelUser->findByEmail($sortedData[$i]['Email'])) {
                                                    print_r(" 10 ");
                                                    // echo '<pre>';
                                                    // print_r($modelUser);
                                                    // echo '</pre>';
                                                    // die();
                                                } else {
                                                    $modelUser->first_name = $sortedData[$i]['First Name'];
                                                    $modelUser->last_name = $sortedData[$i]['Last Name'];
                                                    $modelUser->matric_number = $sortedData[$i]['Matriculation Number'];
                                                    $modelUser->email = $sortedData[$i]['Email'];
                                                    $modelUser->type = '1';
                                                    $modelUser->generateAuthKey();
                                                    $modelUser->save();
                                                    print_r(" 11 ");
                                                }

                                                $modelPeerAssessment = new PeerAssessment();
                                                $modelPeerAssessment->student_id = $modelUser->id;
                                                $modelPeerAssessment->group_id = $temp_Group_id;

                                                if($flag = $modelPeerAssessment->save(false)) {
                                                    print_r(" 12 ");
                                                } else {
                                                    print_r(" 13 ");
                                                    $flag = false;
                                                    break;
                                                }
                                            } else {
                                                print_r(" 14 ");
                                                $flag = false;
                                                break;
                                            }
                                        }
                                    }

                                    $i++;
                                    print_r(" 15 ");
                                } while ($i < count($sortedData));
                                print_r(" 16 ");
                            }

                            foreach ($modelsSection as $indexSection => $modelSection) {

                                if ($flag === false) {
                                    break;  
                                }
    
                                $modelSection->assessment_id = $model->id;

                                if (!($flag = $modelSection->save(false))) {
                                    break;
                                }

                                if (isset($modelsItem[$indexSection]) && is_array($modelsItem[$indexSection])) {
                                    foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) {

                                        $modelItem->section_id = $modelSection->id;
    
                                        if (!($flag = $modelItem->save(false))) {
                                            break;
                                        }

                                        if (isset($modelsRubric[$indexSection][$indexItem]) && is_array($modelsRubric[$indexSection][$indexItem])) {
                                            foreach ($modelsRubric[$indexSection][$indexItem] as $indexRubric => $modelRubric) {
        
                                                if (!empty($modelRubric->level) && !empty($modelRubric->weight) && !empty($modelRubric->description)) {
                                                    $modelRubric->item_id = $modelItem->id;
            
                                                    if (!($flag = $modelRubric->save(false))) {
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if($flag) {

                                    $modelLecturerAssessment = new LecturerAssessment();

                                    $modelLecturerAssessment->assessment_id = $model->id;
                                    $modelLecturerAssessment->lecturer_id = Yii::$app->user->id;

                                    if (!($flag = $modelLecturerAssessment->save(false))) {
                                        break;
                                    }

                                    $coordinatorList = $_POST['LecturerAssessment']['lecturer_id'];

                                    foreach ($coordinatorList as $coorinator) {

                                        $modelLecturerAssessment = new LecturerAssessment();
                                        $modelLecturerAssessment->assessment_id = $model->id;
                                        $modelLecturerAssessment->lecturer_id = $coorinator;
                                        
                                        if (!($flag = $modelLecturerAssessment->save(false))) {
                                            break;
                                        }
                                    }

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
            'modelLecturerAssessment' => $modelLecturerAssessment,
            'coordinators' => $coordinators,
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

    public function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}

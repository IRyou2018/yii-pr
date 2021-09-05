<?php

use common\models\GroupStudentInfo;
use yii\bootstrap4\Modal;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="view-feedback">

    <h2>Assessment: <?= Html::encode($this->title) ?></h2>
    <h2>Grade: <?= $grade ?></h2>

    <?php if (!empty($modelsSection)) : ?>
        <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
        <div class="card mt-2 mb-2">
            <div class="card-header text-white bg-dark">
                <h5><?= $modelSection->name ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container border mb-2">
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-info">
                            Item/Functionality
                        </div>
                        <div class="col-md-10 text-white bg-info">
                            <?= $modelItem->name ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Mark
                        </div>
                        <div class="col-md-7">
                            <?= $feedbackDetail[$indexSection][$indexItem]['mark'] . " / " . $modelItem->max_mark_value ?>
                        </div>
                        <?php if ($modelSection->section_type == 0) : ?>
                            <?php if ($model->assessment_type == 0
                                || $model->assessment_type == 1
                                || $model->assessment_type == 2) : ?>
                                <div class="col-md-3">
                                    <?php
                                        $group_id = GroupStudentInfo::findOne($id)->group_id;
                                        $commentQuery = (new \Yii\db\Query())
                                            ->select('gad.comment as comment')
                                            ->from('group_assessment_detail as gad')
                                            ->join('INNER JOIN', 'group_student_info as gsa', 'gad.group_student_Info_id = gsa.id')
                                            ->join('INNER JOIN', 'group_assessment as ga', 'gsa.group_id = ga.id')
                                            ->where('gad.item_id = :item_id')
                                            ->andWhere('gad.work_student_id = :student_id')
                                            ->andWhere('ga.id = :group_id')
                                            ->andWhere('gsa.id  <> :id')
                                            ->addParams([':group_id' => $group_id,
                                                        ':item_id' => $modelItem->id,
                                                        ':student_id' => Yii::$app->user->id,
                                                        ':id' => $id])
                                            ->all();

                                        $dataProvider = new ArrayDataProvider([
                                            'allModels' => $commentQuery
                                        ]);
                                                                            
                                        Modal::begin([
                                                'title' => 'Comments from others',
                                                'toggleButton' => ['label' => 'Comments from others', 'class' => 'btn btn-info btn-sm'],
                                            ]);
                                        ?>

                                        <?= GridView::widget([
                                            'dataProvider' => $dataProvider,
                                            'id' => 'coordinatorList',
                                            'tableOptions' => ['class' => 'table table-bordered'],
                                            'summary' => false,
                                            'columns' => [
                                                [
                                                    'attribute' => 'comment',
                                                    'value' => 'comment',
                                                    'label' => 'Comments',
                                                    'headerOptions' => ['class' => 'text-light bg-dark']
                                                ]
                                            ],
                                        ]); ?>
                                    <?php Modal::end(); ?>
                                </div>
                            <?php elseif ($model->assessment_type == 4) : ?>
                                <div class="col-md-3">
                                    <?php
                                        $commentQuery = (new \Yii\db\Query())
                                            ->select('iad.comment as comment')
                                            ->from('individual_assessment_detail as iad')
                                            ->join('INNER JOIN', 'marker_student_info as msa', 'iad.marker_student_info_id  = msa.id')
                                            ->join('INNER JOIN', 'individual_assessment as ia', 'msa.individual_assessment_id = ia.id')
                                            ->where('iad.item_id = :item_id')
                                            ->andWhere('ia.student_id = :student_id')
                                            ->andWhere('ia.id = :id')
                                            ->addParams([':item_id' => $modelItem->id,
                                                        ':student_id' => Yii::$app->user->id,
                                                        ':id' => $id])
                                            ->all();

                                        $dataProvider = new ArrayDataProvider([
                                            'allModels' => $commentQuery
                                        ]);
                                                                            
                                        Modal::begin([
                                                'title' => 'Comments from others',
                                                'toggleButton' => ['label' => 'Comments from others', 'class' => 'btn btn-info btn-sm'],
                                                'size' => 'modal-lg',
                                            ]);
                                        ?>

                                        <?= GridView::widget([
                                            'dataProvider' => $dataProvider,
                                            'id' => 'coordinatorList',
                                            'tableOptions' => ['class' => 'table table-bordered'],
                                            'summary' => false,
                                            'columns' => [
                                                [
                                                    'attribute' => 'comment',
                                                    'value' => 'comment',
                                                    'label' => 'Comments',
                                                    'headerOptions' => ['class' => 'text-light bg-dark'],
                                                    'contentOptions' => ['class' => 'text-wrap']
                                                ]
                                            ],
                                        ]); ?>
                                    <?php Modal::end(); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Comment
                        </div>
                        <div class="col-md-10">
                            <?= $feedbackDetail[$indexSection][$indexItem]['comment'] ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

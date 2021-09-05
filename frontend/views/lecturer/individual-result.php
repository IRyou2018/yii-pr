<?php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Individual Results';
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="individual-result">

    <h4>Assessment : <?= $model->name ?></h4>
    <h5>Student : <?= $workStudentName ?></h5>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="card mt-2 mb-2">
        <div class="card-header text-white bg-dark">
            <h5><?= $modelSection->name ?></h5>
        </div>
        <div class="card-body">
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
            <div class="container border mb-2">
            <div class="row mb-1">
                <div class="col-md-2 font-weight-bold text-white bg-info">
                    Item/Functionality
                </div>
                <div class="col-md-10 font-weight-bold text-white bg-info">
                    <?= $modelItem->name ?>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-md-2 text-white bg-secondary">
                    Max Mark
                </div>
                <div class="col-md-2">
                    <?= $modelItem->max_mark_value ?>
                </div>
            </div>
            <?php if ($modelSection->section_type == 0) : ?>
                <?php if ($model->assessment_type == 4) : ?>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Marker Student
                        </div>
                        <?php foreach ($modelsReviewDetail[$indexSection][$indexItem] as $reviewDetail): ?>
                        <div class="col text-white bg-secondary">
                            <?= $reviewDetail->markerStudentInfo->studentName ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Proposed Mark
                        </div>
                        <?php foreach ($modelsReviewDetail[$indexSection][$indexItem] as $reviewDetail): ?>
                        <div class="col">
                            <?= $reviewDetail->mark ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Supposed Mark
                        </div>
                        <div class="col">
                            <?= $supposedMarkList[$indexSection][$indexItem] ?>
                        </div>
                    </div>
                <?php elseif ($model->assessment_type == 3) : ?>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Proposed Mark
                        </div>
                        <?php foreach ($modelsReviewDetail[$indexSection][$indexItem] as $reviewDetail): ?>
                        <div class="col">
                            <?= $reviewDetail->mark ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="row mb-1">
                <div class="col-md-2 text-white bg-secondary">
                    <?php 
                        if ($modelSection->section_type == 0) {
                            echo 'Actual Mark';
                        } else if ($modelSection->section_type == 1) {
                            echo 'Mark';
                        }
                    ?>
                </div>
                <div class="col">
                    <?= $modelsIndividualFeedback[$indexSection][$indexItem]->mark ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-2 text-white bg-secondary">
                    Your Comment
                </div>
                <div class="col">
                    <?= $modelsIndividualFeedback[$indexSection][$indexItem]->comment ?>    
                </div>
            </div>
            </div>

            <?php if ($modelSection->section_type == 0) : ?>
            <div class="comment container border mb-5">
                <div class="row mb-1">
                    <div class="col-md-12 text-white bg-secondary">
                        Comments from student
                    </div>
                </div>
                <?php foreach ($modelsReviewDetail[$indexSection][$indexItem] as $reviewDetail): ?>
                <div class="row mt-1">
                    <div class="col-md-2 text-white bg-secondary">
                        <?= $reviewDetail->markerStudentInfo->studentName ?>
                    </div>
                    <div class="col-md-10 ">
                        <?= $reviewDetail->comment ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>

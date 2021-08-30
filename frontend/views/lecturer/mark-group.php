<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Mark Group';
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mark-group">

    <h4>Assessment : <?= $model->name ?></h4>

    <?php $form = ActiveForm::begin(); ?>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="card mt-2 mb-2">
        <div class="card-header text-white bg-dark">
            <h5><?= $modelSection->name ?></h5>
        </div>
        <div class="card-body">
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container border mb-2">
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold text-white bg-primary">
                            Item/Functionality
                        </div>
                        <div class="col-md-10 font-weight-bold text-white bg-primary">
                            <?= $modelItem->name ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Group Mark
                        </div>
                        <div class="col-md-4">
                            &nbsp&nbsp<?php foreach ($groupGrades as $groupGrade)
                                if ($groupGrade->item_id == $modelItem->id) {
                                    echo $groupGrade->mark;
                                }
                            ?>
                        </div>
                        <div class="col-md-2 text-white bg-secondary">
                            Max Mark
                        </div>
                        <div class="col-md-4">
                            &nbsp&nbsp&nbsp<?= $modelItem->max_mark_value ?>
                        </div>
                    </div>
                    <?php if ($modelSection->section_type == 0) : ?>
                        <?php if ($model->assessment_type == 0 || $model->assessment_type == 2) : ?>
                            <div class="row mb-1">
                                <div class="col-md-2 text-white bg-secondary">
                                    Contribution
                                </div>
                                <?php foreach ($groupStudentInfos as $workStudent): ?>
                                    <div class="col text-white bg-secondary">
                                        <?= $workStudent->studentName ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php foreach ($groupStudentInfos as $indexMarker => $markerStudent): ?>
                            <div class="row mb-1">
                                <div class="col-md-2 text-white bg-secondary">
                                    <?= $markerStudent->studentName ?>
                                </div>
                                <?php foreach ($modelsReviewDetail[$indexSection][$indexItem][$indexMarker] as $reviewDetail): ?>
                                    <div class="col">
                                        &nbsp&nbsp<?= $reviewDetail->contribution ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php elseif ($model->assessment_type == 1) : ?>
                            <div class="row mb-1">
                                <div class="col-md-2 text-white bg-secondary">
                                    Proposed Mark
                                </div>
                                <?php foreach ($groupStudentInfos as $workStudent): ?>
                                    <div class="col text-white bg-secondary">
                                        <?= $workStudent->studentName ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php foreach ($groupStudentInfos as $indexMarker => $markerStudent): ?>
                            <div class="row mb-1">
                                <div class="col-md-2 text-white bg-secondary">
                                    <?= $markerStudent->studentName ?>
                                </div>
                                <?php foreach ($modelsReviewDetail[$indexSection][$indexItem][$indexMarker] as $reviewDetail): ?>
                                    <div class="col">
                                        &nbsp&nbsp<?= $reviewDetail->mark ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="row mb-1">
                            <div class="col-md-2 text-white bg-secondary">
                                Supposed Mark
                            </div>
                            <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                                <div class="col">
                                    &nbsp&nbsp<?= $supposedMarkList[$indexSection][$indexItem][$indexWork] ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row mb-1">
                            <div class="col-md-2 text-white bg-secondary">
                                Actual Mark
                            </div>
                            <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                                <div class="col">
                                    <?php
                                        // necessary for update action.
                                        if (!$modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork]->isNewRecord) {
                                            echo Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]id");
                                        }
                                    ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]item_id"); ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]group_id"); ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]student_id"); ?>
                                    <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]mark")->textInput(['style'=>'width:76px', 'class'=>'text-center'])->label(false) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12 text-white bg-secondary">
                                Your Comment
                            </div>
                        </div>
                        <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                            <div class="row mb-2">
                                <div class="col-md-2 text-white bg-secondary">
                                    <?= $workStudent->studentName ?>
                                </div>
                                <div class="col">
                                    <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]comment")->textarea(['maxlength' => true])->label(false) ?>    
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($modelSection->section_type == 1) : ?>
                        <?php if ($modelItem->item_type == 0) : ?>
                            <div class="row mb-2">
                                <div class="col-md-2 text-white bg-secondary">
                                    
                                </div>
                                <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                                <div class="col text-white bg-secondary">
                                    <?= $workStudent->studentName ?>  
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-2 text-white bg-secondary">
                                    Mark
                                </div>
                                <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                                <div class="col">
                                    <?php
                                        // necessary for update action.
                                        if (!$modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork]->isNewRecord) {
                                            echo Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]id");
                                        }
                                    ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]item_id"); ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]group_id"); ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]student_id"); ?>
                                    <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]mark")->textInput(['style'=>'width:76px', 'class'=>'text-center'])->label(false) ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12 text-white bg-secondary">
                                    Your Comment
                                </div>
                                </div>
                                <?php foreach ($groupStudentInfos as $indexWork => $workStudent): ?>
                                    <div class="row mb-2">
                                        <div class="col-md-2 text-white bg-secondary">
                                            <?= $workStudent->studentName ?>
                                        </div>
                                        <div class="col">
                                            <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][$indexWork], "[{$indexSection}][{$indexItem}][{$indexWork}]comment")->textarea(['maxlength' => true])->label(false) ?>    
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                        <?php elseif ($modelItem->item_type == 1) : ?>
                            <div class="row mb-2">
                                <div class="col-md-2 text-white bg-secondary">
                                    Mark
                                </div>
                                <div class="col">
                                    <?php
                                        // necessary for update action.
                                        if (!$modelsGroupAssessmentFeedback[$indexSection][$indexItem][0]->isNewRecord) {
                                            echo Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][0]id");
                                        }
                                    ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][0]item_id"); ?>
                                    <?= Html::activeHiddenInput($modelsGroupAssessmentFeedback[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][0]group_id"); ?>
                                    <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][0]mark")->textInput(['style'=>'width:76px', 'class'=>'text-center'])->label(false) ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-2 text-white bg-secondary">
                                    Your Comment
                                </div>
                                <div class="col">
                                    <?= $form->field($modelsGroupAssessmentFeedback[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][0]comment")->textarea(['maxlength' => true])->label(false) ?>    
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

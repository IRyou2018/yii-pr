<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Mark Individual';
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="individual-form">

    <h4>Assessment : <?= $model->name ?></h4>
    <h5>Student : <?= $workStudentName ?></h5>

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
                    Max Mark
                </div>
                <div class="col-md-2">
                    &nbsp&nbsp&nbsp<?= $modelItem->max_mark_value ?>
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
                            &nbsp&nbsp&nbsp<?= $reviewDetail->mark ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Supposed Mark
                        </div>
                        <div class="col">
                            &nbsp&nbsp&nbsp<?= $supposedMarkList[$indexSection][$indexItem] ?>
                        </div>
                    </div>
                    <?php elseif ($model->assessment_type == 3) : ?>
                        <div class="row mb-1">
                            <div class="col-md-2 text-white bg-secondary">
                                Proposed Mark
                            </div>
                            <?php foreach ($modelsReviewDetail[$indexSection][$indexItem] as $reviewDetail): ?>
                            <div class="col">
                                &nbsp&nbsp&nbsp<?= $reviewDetail->mark ?>
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
                    <?php
                        // necessary for update action.
                        if (!$modelsIndividualFeedback[$indexSection][$indexItem]->isNewRecord) {
                            echo Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]id");
                        }
                    ?>
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]individual_assessment_id"); ?>
                    <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['style'=>'width:76px', 'class'=>'text-center'])->label(false) ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-2 text-white bg-secondary">
                    Your Comment
                </div>
                <div class="col">
                    <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]comment")->textarea(['maxlength' => true])->label(false) ?>    
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

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

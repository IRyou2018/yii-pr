<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Mark Individual';
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="individual-result">

    <h4>Assessment : <?= $model->name ?></h4>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="card mt-2">
        <div class="card-header">
            <?= $modelSection->name ?>
        </div>
        <div class="card-body">
        <?php if ($modelSection->section_type == 0) : ?>
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container">
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Item/Functionality
                    </div>
                    <div class="col-md-10">
                        <?= $modelItem->name ?>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Actual Mark
                    </div>
                    <div class="col-md-2">
                        <?= $modelsIndividualFeedback[$indexSection][$indexItem]->mark ?>
                    </div>
                    <div class="col-md-2 bg-light">
                        Max Mark
                    </div>
                    <div class="col-md-2">
                        <?= $modelItem->max_mark_value ?>
                    </div>
                    <div class="col-md-2 bg-light">
                        Proposed Mark
                    </div>
                    <div class="col-md-1">
                        <?= $modelsReviewDetail[$indexSection][$indexItem]->mark ?>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Lecturer Comment
                    </div>
                    <div class="col-md-10">
                        <?= $modelsIndividualFeedback[$indexSection][$indexItem]->comment ?>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Student Comment
                    </div>
                    <div class="col-md-10">
                        <?= $modelsReviewDetail[$indexSection][$indexItem]->comment ?>
                    </div>
                </div>
                </div>
            <?php endforeach; ?>
        <?php elseif ($modelSection->section_type == 1) : ?>
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container">
                <div class="row">
                    <div class="col-md-2 bg-light">
                        Item/Functionality
                    </div>
                    <div class="col-md-10">
                        <?= $modelItem->name ?>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Actual Mark
                    </div>
                    <div class="col-md-2">
                        <?= $modelsIndividualFeedback[$indexSection][$indexItem]->mark ?>
                    </div>
                    <div class="col-md-2 bg-light">
                        Max Mark
                    </div>
                    <div class="col-md-6">
                        <?= $modelItem->max_mark_value ?>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        Lecturer Comment
                    </div>
                    <div class="col-md-10">
                        <?= $modelsIndividualFeedback[$indexSection][$indexItem]->comment ?>
                    </div>
                </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

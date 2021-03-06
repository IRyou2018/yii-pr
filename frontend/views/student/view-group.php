<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="view-group">

    <h2><?= Html::encode($this->title) ?></h2>

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
                    <div class="col-md-2 font-weight-bold text-white bg-info">
                        Item/Functionality
                    </div>
                    <div class="col-md-10 text-white bg-info">
                        <?= $modelItem->name ?>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-md-2 font-weight-bold text-white bg-secondary">
                        Max Mark
                    </div>
                    <div class="col-md-2">
                        &nbsp&nbsp&nbsp<?= $modelItem->max_mark_value ?>
                    </div>
                    <?php if ($model->assessment_type == 2) : ?>
                    <div class="col-md-2 font-weight-bold text-white bg-secondary">
                        Proposed Mark
                    </div>
                    <div class="col-md-2">
                        &nbsp&nbsp&nbsp<?= $marklist[$indexSection][$indexItem] ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row mb-1">
                    <div class="col-md-2 font-weight-bold text-white bg-secondary">
                        Student Name
                    </div>
                    <div class="col-md-2 font-weight-bold text-white bg-secondary">
                    <?php
                        if ($model->assessment_type == 0 || $model->assessment_type == 2) {
                            echo 'Contribution';
                        } else if ($model->assessment_type == 1) {
                            echo 'Proposed Mark';
                        }
                    ?>   
                    </div>
                    <div class="col-md-8 font-weight-bold text-white bg-secondary">
                        Comment
                    </div>
                </div>
                <?php foreach ($modelsGroupAssessmentDetail[$indexSection][$indexItem] as $indexStudent => $groupDetail) : ?>
                <div class="row mb-1">
                    <div class="col-md-2 bg-light">
                        <?= $groupDetail->studentName ?>
                    </div>
                    <div class="col-md-2">
                        <?php if ($model->assessment_type == 0 || $model->assessment_type == 2) : ?>
                            &nbsp&nbsp&nbsp<?= $groupDetail->contribution ?>
                        <?php elseif ($model->assessment_type == 1) : ?>
                            &nbsp&nbsp&nbsp<?= $groupDetail->mark ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <?= $groupDetail->comment ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

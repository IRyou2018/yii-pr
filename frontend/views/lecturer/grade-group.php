<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = 'Grade Group';
$this->params['breadcrumbs'][] = ['label' => 'Assessment', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="grade-group">

    <h4>Assessment: <?= $model->name ?></h4>
    <h4>Group: <?= $groupAssessmentInfo->name ?></h4>

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
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
                            Group Mark
                        </div>
                        <div class="col-md-4">
                            <?= Html::activeHiddenInput($modelsGroupAssessmentGrade[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                            <?= Html::activeHiddenInput($modelsGroupAssessmentGrade[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]group_id"); ?>
                            <?= $form->field($modelsGroupAssessmentGrade[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                        </div>
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
                            Max Mark
                        </div>
                        <div class="col-md-4">
                            <?= $modelItem->max_mark_value ?>
                        </div>
                    </div>
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

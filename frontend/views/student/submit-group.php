<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="submit-group">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin(); ?>

    <?php if (!empty($modelsSection)) : ?>
        <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
        <div class="card mt-2 mb-2">
            <div class="card-header text-white bg-dark">
                <h5><?= $modelSection->name ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="row mb-1">
                    <div class="col-md-2 text-white bg-secondary">
                        Item/Functionality
                    </div>
                    <div class="col-md-10">
                        <?= $modelItem->name ?>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-md-2 text-white bg-secondary">
                        Max Mark
                    </div>
                    <div class="col-md-2">
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?= $modelItem->max_mark_value ?>
                    </div>
                    <?php if ($model->assessment_type == 2) : ?>
                    <div class="col-md-2 text-white bg-secondary">
                        Proposed Mark
                    </div>
                    <div class="col-md-2">
                        <?= Html::activeHiddenInput($modelsGroupItemMark[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_max_mark"); ?>
                        <?= $form->field($modelsGroupItemMark[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row mb-1">
                    <div class="col-md-2 text-white bg-secondary">
                        Student Name
                    </div>
                    <div class="col-md-2 text-white bg-secondary">
                    <?php
                        if ($model->assessment_type == 0 || $model->assessment_type == 2) {
                            echo 'Contribution';
                        } else if ($model->assessment_type == 1) {
                            echo 'Proposed Mark';
                        }
                    ?>   
                    </div>
                    <div class="col-md-8 text-white bg-secondary">
                        Comment
                    </div>
                </div>
                <?php foreach ($modelsGroupAssessmentDetail[$indexSection][$indexItem] as $indexStudent => $groupDetail) : ?>
                <div class="row mb-1">
                    <div class="col-md-2 bg-light">
                        <?= $groupDetail->studentName ?>
                    </div>
                    <div class="col-md-2">
                        <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]item_id"); ?>
                        <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]group_student_Info_id"); ?>
                        <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]work_student_id"); ?>
                        <?php if ($model->assessment_type == 0 || $model->assessment_type == 2) : ?>
                            <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]contribution")->textInput(['style'=>'width:70px', 'class'=>'text-center', 'type' => 'number'])->label(false) ?>
                        <?php elseif ($model->assessment_type == 1) : ?>
                            <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]comment")->textarea(['maxlength' => true])->label(false) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php $style= <<< CSS

input[type=number]::-webkit-inner-spin-button {
  cursor: pointer;
}

CSS;
$this->registerCss($style);
?>


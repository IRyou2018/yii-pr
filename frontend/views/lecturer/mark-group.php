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

    <?php $form = ActiveForm::begin(); ?>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <table class="table table-bordered mt-2">
        <thead>
            <tr class="text-white bg-dark">
                <th class="col" colspan="4">
                    <?= $modelSection->name ?>
                </th>
            </tr>
            <tr class="text-white bg-secondary">
                <th class="align-middle">Item/Functionality</th>
                <th class="align-middle text-center">Max Mark</th>
                <th class="align-middle text-center">
                    <?php 
                        if ($modelSection->section_type == 0) {
                            echo 'Proposed Mark';
                        } else if ($modelSection->section_type == 1) {
                            echo 'Mark';
                        }
                    ?>
                </th>
                <th class="align-middle">
                    <?php 
                        if ($modelSection->section_type == 0) {
                            echo 'Student Comment';
                        } else if ($modelSection->section_type == 1) {
                            echo 'Comment';
                        }
                    ?>
                </th>
            </tr>
        </thead>

        <tbody class="container-section">
            <?php if ($modelSection->section_type == 0) : ?>
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
            <tr>
                <td class="col-md-2 bg-light">
                    <?= $modelItem->name ?>
                </td>
                <td class="col-md-1 text-center">
                    <?= $modelItem->max_mark_value ?>
                </td>
                <td class="col-md-1 text-center">
                    <?= $modelsReviewDetail[$indexSection][$indexItem]->mark ?>
                </td>
                <td class="col-md-7">
                    <?= $modelsReviewDetail[$indexSection][$indexItem]->comment ?>
                </td>
            </tr>
            <tr>
                <td class="col-md-2 bg-light">
                    Actual Mark
                </td>
                <td class="col-md-1" colspan="3">
                    <?php
                        // necessary for update action.
                        if (!$modelsIndividualFeedback[$indexSection][$indexItem]->isNewRecord) {
                            echo Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]id");
                        }
                    ?>
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]marker_student_info_id"); ?>
                    <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['style'=>'width:76px', 'class'=>'text-center'])->label(false) ?>
                </td>
            </tr>
            <tr>
                <td class="col-md-2 bg-light">
                    Your Comment
                </td>
                <td class="col-md-1" colspan="3">
                    <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]comment")->textarea(['maxlength' => true])->label(false) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php elseif ($modelSection->section_type == 1) : ?>
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <tr>
                    <td class="col-md-2"> 
                        <?= $modelItem->name ?>
                    </td>
                    <td class="col-md-1">
                        <?= $modelItem->max_mark_value ?>
                    </td>
                    <td class="col-md-1">
                        <?php
                            // necessary for update action.
                            if (!$modelsIndividualFeedback[$indexSection][$indexItem]->isNewRecord) {
                                echo Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]id");
                            }
                        ?>
                        <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                        <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]marker_student_info_id"); ?>
                        <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['maxlength' => true])->label(false) ?>
                    </td>
                    <td class="col-md-7">
                        <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]comment")->textarea(['maxlength' => true])->label(false) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

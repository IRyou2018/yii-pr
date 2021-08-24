<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Create Assessments';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assessments-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <table class="table table-bordered mt-2">
        <thead>
            <tr class="text-white bg-secondary">
                <th scope="col" colspan="4">
                    <?= $modelSection->name ?>
                </th>
            </tr>
            <tr class="text-white bg-dark">
                <th class="align-middle">Item/Functionality</th>
                <th class="align-middle">Max Mark</th>
                <th class="align-middle">Proposed Mark
                    </th>
                <th class="align-middle">Actual Mark</th>
            </tr>
        </thead>

        <tbody class="container-section">
            <?php if ($modelSection->section_type == 0) : ?>
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
            <tr>
                <td class="col-md-2">
                    <?= $modelItem->name ?>
                </td>
                <td class="col-md-1">
                    <?= $modelItem->max_mark_value ?>
                </td>
                <td class="col-md-1">
                    <?= $modelsPeerReviewDetail[$indexSection][$indexItem]->mark ?>
                </td>
                <td class="col-md-7">
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                    <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]peer_review_id"); ?>
                    <?= $form->field($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textarea(['maxlength' => true])->label(false) ?>
                </td>
            </tr>
            <tr>
                <td class="col-md-2 text-white bg-secondary">
                    Marker Student Feedback
                </td>
                <td class="col-md-1" colspan="3">
                    <?= $modelsPeerReviewDetail[$indexSection][$indexItem]->comment ?>
                </td>
            </tr>
            <tr>
                <td class="col-md-2 text-white bg-secondary">
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
                        <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                        <?= Html::activeHiddenInput($modelsIndividualFeedback[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]peer_review_id"); ?>
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

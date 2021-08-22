<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

?>
<div class="assessments-view">

    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <table class="table table-bordered mt-2">
        <thead>
            <tr class="text-white bg-secondary">
                <th scope="col" colspan="12">
                    <?= $modelSection->name ?>
                </th>
            </tr>
            <tr class="text-white bg-dark">
                <th class="align-middle">Item/Functionality</th>
                <th class="align-middle">Max Mark</th>
                <th class="align-middle">Proposed Mark</th>
                <th class="align-middle">Comment</th>
            </tr>
        </thead>

        <tbody class="container-section">
            <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
            <tr>
                <td class="col-md-2"> 
                    <?= $modelItem->name ?>
                </td>
                <td class="col-md-1">
                    <?= $modelItem->max_mark_value ?>
                </td>
                <td class="col-md-1"> 
                    <?= $form->field($modelsPeerReviewDetail[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['maxlength' => true])->label(false) ?>
                </td>
                <td class="col-md-7">
                    <?= $form->field($modelsPeerReviewDetail[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]comment")->textarea(['maxlength' => true])->label(false) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <?php ActiveForm::end(); ?>

</div>

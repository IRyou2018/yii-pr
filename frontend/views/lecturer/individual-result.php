<?php

use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
// $this->params['breadcrumbs'][] = $this->title;
// \yii\web\YiiAsset::register($this);
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
                    <?= Html::activeHiddenInput($modelsPeerReviewDetail[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_id"); ?>
                    <?= Html::activeHiddenInput($modelsPeerReviewDetail[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]peer_review_id"); ?>
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

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="assessments-form">

    <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assessment_type')->textInput() ?>

    <?= $form->field($model, 'deadline')->textInput() ?>

    <?= $form->field($model, 'active')->textInput() ?>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-section', // required: css class selector
        'widgetItem' => '.section', // required: css class
        'limit' => 10, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-section', // css class
        'deleteButton' => '.remove-section', // css class
        'model' => $modelsSection[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'name',
            'section_type'
        ],
    ]); ?>
    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="container-section"><!-- widgetContainer -->

        <div class="section card"><!-- widgetBody -->
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>Section</h3>
                    </div>
                    <div class="col-md-2 float-right">
                        <button type="button" class="add-section btn btn-success btn-xs"><span class="material-icons">add</span></button>
                        <button type="button" class="remove-section btn btn-danger btn-xs"><span class="material-icons">remove</span></button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                    // necessary for update action.
                    if (! $modelSection->isNewRecord) {
                        echo Html::activeHiddenInput($modelSection, "[{$indexSection}]id");
                    }
                ?>
                <div class="row">
                    <div class="col-sm-7">
                        <?= $form->field($modelSection, "[{$indexSection}]name")->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-sm-3">
                        <?= $form->field($modelSection, "[{$indexSection}]section_type")->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $this->render('_form-items', [
                                'form' => $form,
                                'indexSection' => $indexSection,
                                'modelsItem' => $modelsItem[$indexSection],
                            ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php DynamicFormWidget::end(); ?>



    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
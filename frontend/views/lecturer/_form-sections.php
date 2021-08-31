<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_section', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
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

<div class="card mt-2 mb-2"><!-- widgetBody -->
    <div class="card-header text-white bg-dark">
        <div class="row">
            <div class="col-md-11">
                <h5>Section</h5>
            </div>
            <div class="col-md-1 text-right">
                <button type="button" class="add-section btn-success btn-xs"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="card-body container-section">
        <div class="row section mb-3">
            <div class="col">
                <div class="row">
                    <div class="col-md-2">
                        <span class="align-middle">Section Name</span>
                    </div>
                    <div class="col-md-5">
                        <?php
                            // necessary for update action.
                            if (!$modelSection->isNewRecord) {
                                echo Html::activeHiddenInput($modelSection, "[{$indexSection}]id");
                            }
                        ?>
                        <?= $form->field($modelSection, "[{$indexSection}]name")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2 align-middle">
                    <span class="align-middle">Section Type</span>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($modelSection, "[{$indexSection}]section_type")->label(false)->dropdownList(
                                [
                                    0 => 'For Student',
                                    1 => 'Lecturer Only'
                                ],
                                ['prompt'=>'Select Section Type']
                        ) ?>
                    </div>
                    <div class="col-md-1 text-right" rowspan="2">
                        <button type="button" class="remove-section btn-danger btn-xs"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-11">
                        <?= $this->render('_form-items', [
                            'form' => $form,
                            'indexSection' => $indexSection,
                            'modelsItem' => $modelsItem,
                            'modelsRubric' => $modelsRubric,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <?php endforeach; ?>
</div>
 <?php DynamicFormWidget::end(); ?>
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
    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
    <div class="container-section"><!-- widgetContainer -->
        <div class="section card mt-3"><!-- widgetBody -->
            <div class="card-header text-white bg-dark">
                <div class="row">
                    <div class="col-md-10">
                        <h4>Section</h4>
                    </div>
                    <div class="col-md-2 float-right">
                        <button type="button" class="add-section btn-success btn-xs"><span class="material-icons">add</span></button>
                        <button type="button" class="remove-section btn-danger btn-xs"><span class="material-icons">remove</span></button>
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
                                'modelsRubric' => $modelsRubric,
                            ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php DynamicFormWidget::end(); ?>
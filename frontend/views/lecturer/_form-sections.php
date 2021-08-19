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

<table class="table table-bordered mt-2">
    <thead>
        <tr class="text-white bg-dark">
            <th colspan="2"><h4>Sections</h4></th>
            <th class="text-center" style="width: 90px;">
                <button type="button" class="add-section btn-success btn-xs"><i class="fas fa-plus"></i></button>
            </th>
        </tr>
    </thead>

    <tbody class="container-section">
    <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
        <tr class="section">
            <td class="vcenter">
                <?php
                    // necessary for update action.
                    if (! $modelSection->isNewRecord) {
                        echo Html::activeHiddenInput($modelSection, "[{$indexSection}]id");
                    }
                ?>
                
                <?= $form->field($modelSection, "[{$indexSection}]name")->textInput(['maxlength' => true]) ?>

                <?= $form->field($modelSection, "[{$indexSection}]section_type")->textInput(['maxlength' => true]) ?>
            </td>
            <td>
                <?= $this->render('_form-items', [
                    'form' => $form,
                    'indexSection' => $indexSection,
                    'modelsItem' => $modelsItem[$indexSection],
                    'modelsRubric' => $modelsRubric,
                ]) ?>
            </td>
            <td class="text-center vcenter" style="width: 90px;">
                <button type="button" class="remove-section btn-danger btn-xs"><i class="fas fa-minus"></i></button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
 <?php DynamicFormWidget::end(); ?>
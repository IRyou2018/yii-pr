<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_rubric',
    'widgetBody' => '.container-rubric',
    'widgetItem' => '.rubric',
    'limit' => 10,
    'min' => 1,
    'insertButton' => '.add-rubric',
    'deleteButton' => '.remove-rubric',
    'model' => $modelsRubric[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'name',
        'max_mark_value',
        'item_type'
    ],
]); ?>

<div class="card"><!-- widgetBody -->
<div class="card-header">
        <div class="row">
            <div class="col-md-11">
                <h3>Rubric</h3>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Weight</th>
                    <th>Description</th>
                    <th class="text-center">
                        <button type="button" class="add-rubric btn-success"><span class="material-icons">add</span></button>
                    </th>
                </tr>
            </thead>

            <tbody class="container-rubric">
                <?php foreach ($modelsRubric as $indexRubric => $modelRubric) : ?>
                    <tr class="rubric">
                        <td class="vcenter">
                            <?php
                            // necessary for update action.
                            if (!$modelRubric->isNewRecord) {
                                echo Html::activeHiddenInput($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]id");
                            }
                            ?>

                            <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]level")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>
                        <td class="vcenter">
                            <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]weight")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>
                        <td class="vcenter">
                            <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]description")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>

                        <td class="text-center vcenter" style="width: 90px;">
                            <button type="button" class="remove-rubric btn-danger"><span class="material-icons">remove</span></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php DynamicFormWidget::end(); ?>
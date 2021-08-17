<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_item',
    'widgetBody' => '.container-item',
    'widgetItem' => '.item',
    'limit' => 4,
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.remove-item',
    'model' => $modelsItem[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'name',
        'max_mark_value',
        'item_type'
    ],
]); ?>

<table class="table table-bordered">
    <thead>
        <tr class="text-white bg-secondary">
            <th>Items</th>
            <th>Rubrics (optional)</th>
            <th class="text-center" style="width: 90px;">
                <button type="button" class="add-item btn-success btn-xs"><span class="material-icons">add</span></button>
            </th>
        </tr>
    </thead>

    <tbody class="container-item">
    <?php foreach ($modelsItem as $indexItem => $modelItem) : ?>
        <tr class="item">
            <td class="vcenter">
                <?php
                    // necessary for update action.
                    if (!$modelItem->isNewRecord) {
                        echo Html::activeHiddenInput($modelItem, "[{$indexSection}][{$indexItem}]id");
                    }
                ?>
                
                <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]name")->textInput(['maxlength' => true]) ?>

                <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]max_mark_value")->textInput(['maxlength' => true]) ?>

                <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]item_type")->textInput(['maxlength' => true]) ?>
            </td>
            <td>
                <?= $this->render('_form-rubrics', [
                    'form' => $form,
                    'indexSection' => $indexSection,
                    'indexItem' => $indexItem,
                    'modelsRubric' => $modelsRubric[$indexSection][$indexItem],
                ]) ?>
            </td>
            <td class="text-center vcenter" style="width: 90px;">
                <button type="button" class="remove-item btn-danger btn-xs"><span class="material-icons">remove</span></button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php DynamicFormWidget::end(); ?>
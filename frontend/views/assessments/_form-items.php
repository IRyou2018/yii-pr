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

<div class="card"><!-- widgetBody -->
    <div class="card-header">
        <div class="row">
            <div class="col-md-11">
                <h3>Item</h3>
            </div>
            <div class="col-md-1 float-right">
                <button type="button" class="add-item btn btn-success btn-xs"><span class="material-icons">add</span></button>
            </div>
        </div>
    </div>
    <?php foreach ($modelsItem as $indexItem => $modelItem) : ?>
    <div class="container-item card-body">
        <table class="item table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Max Mark Value</th>
                    <th>Item Type</th>
                    <th class="text-center">
                    <button type="button" class="remove-item btn-danger"><span class="material-icons">remove</span></button>
                    </th>
                </tr>
            </thead>
            
            <tbody class="">
                    <tr>
                        <td class="vcenter">
                            <?php
                            // necessary for update action.
                            if (!$modelItem->isNewRecord) {
                                echo Html::activeHiddenInput($modelItem, "[{$indexSection}][{$indexItem}]id");
                            }
                            ?>

                            <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]name")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>
                        <td class="vcenter">
                            <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]max_mark_value")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>
                        <td class="vcenter">
                            <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]item_type")->label(false)->textInput(['maxlength' => true]) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="vcenter"  colspan="3">
                        <?= $this->render('_form-rubrics', [
                            'form' => $form,
                            'indexSection' => $indexSection,
                            'indexItem' => $indexItem,
                            'modelsRubric' => $modelsRubric[$indexSection][$indexItem],
                            ]) ?>
                        </td>
                    </tr>
                    </tbody>
                
            
        </table>
    </div>
    <?php endforeach; ?>
</div>
<?php DynamicFormWidget::end(); ?>
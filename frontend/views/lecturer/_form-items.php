<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_item',
    'widgetBody' => '.container-item',
    'widgetItem' => '.item',
    'limit' => 10,
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.remove-item',
    'model' => $modelsItem[0][0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'name',
        'max_mark_value',
        'item_type'
    ],
]); ?>

<div class="card mt-1"><!-- widgetBody -->
    <div class="card-header text-white bg-dark">
        <div class="row">
            <div class="col-md-11">
                <h5>Items</h5>
            </div>
            <div class="col-md-1 text-right">
                <button type="button" class="add-item btn-success btn-xs"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) : ?>
    <div class="card-body container-item border-bottom">
        <div class="row item mt-2">
            <div class="col">
                <div class="row">
                    <div class="col-md-2 bg-light">
                        <span class="align-middle">Item Name</span>
                    </div>
                    <div class="col-md-9">
                        <?php
                            // necessary for update action.
                            if (!$modelItem->isNewRecord) {
                                echo Html::activeHiddenInput($modelItem, "[{$indexSection}][{$indexItem}]id");
                            }
                        ?>
                        <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]name")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-1 text-right" rowspan="3">
                        <button type="button" class="remove-item btn-danger btn-xs"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 bg-light align-middle">
                        <span class="align-middle">Max Value</span>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]max_mark_value")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2 bg-light align-middle">
                        <span class="align-middle">Item Type</span>
                    </div>
                    <div class="col-md-5">
                        <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]item_type")->label(false)->dropdownList(
                                [
                                    0 => 'Individual Item',
                                    1 => 'Group Item'
                                ],
                                ['prompt'=>'Select Item Type', 'class' => 'itemType'],
                        ) ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-11">
                        <?= $this->render('_form-rubrics', [
                            'form' => $form,
                            'indexSection' => $indexSection,
                            'indexItem' => $indexItem,
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
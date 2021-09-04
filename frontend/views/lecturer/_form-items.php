<?php

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

<?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem) : ?>
<div class="container-item">
    <div class="card item mb-2"><!-- widgetBody -->
        <div class="card-header text-white bg-primary">
            <div class="row">
                <div class="col-md-10">
                    <h5>Item for the section</h5>
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" class="add-item btn-success btn-xs"><i class="fas fa-plus"></i></button>
                    <button type="button" class="remove-item btn-danger btn-xs"><i class="fas fa-minus"></i></button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <span class="align-middle">Item Name</span>
                </div>
                <div class="col-md-10">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]name")->label(false)->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 align-middle">
                    <span class="align-middle">Max Value</span>
                </div>
                <div class="col-md-5">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]max_mark_value")->label(false)->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-2 align-middle">
                    <span class="align-middle">Item Type</span>
                </div>
                <div class="col-md-3">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]item_type")->label(false)->dropdownList(
                            [
                                0 => 'Individual Item',
                                1 => 'Group Item'
                            ],
                            ['prompt'=>'Select Item Type', 'class' => 'itemType', 'style' => 'width:200px'],
                    ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
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

<?php DynamicFormWidget::end(); ?>